<?php

namespace Tests\Feature\Trading;

use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RateService;
use App\Services\TradeService;
use App\Services\WalletService;
use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuySellSwapTest extends TestCase
{
    use RefreshDatabase;

    protected CryptoAsset $btc;

    protected CryptoAsset $eth;

    protected FiatCurrency $ngn;

    protected FiatCurrency $usd;

    protected function setUp(): void
    {
        parent::setUp();

        $this->btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $this->eth = CryptoAsset::create(['name' => 'Ethereum', 'symbol' => 'ETH', 'decimal_places' => 8, 'is_active' => true]);
        $this->ngn = FiatCurrency::create(['code' => 'NGN', 'name' => 'Naira', 'symbol' => '₦', 'exchange_rate_to_usd' => 0.00062, 'is_active' => true]);
        $this->usd = FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);
    }

    public function test_admin_can_set_a_24_hour_rate_and_it_expires_correctly(): void
    {
        $admin = User::factory()->create();
        $rate = app(RateService::class)->setRate($this->btc->id, $this->ngn->id, 64000000, 65000000, 24, $admin);

        $this->assertTrue($rate->is_active);
        $this->assertEqualsWithDelta(now()->addHours(24)->timestamp, $rate->expires_at->timestamp, 5);
    }

    public function test_buy_quote_uses_the_active_sell_rate(): void
    {
        $admin = User::factory()->create();
        app(RateService::class)->setRate($this->btc->id, $this->ngn->id, 64000000, 65000000, 24, $admin);

        $quote = app(TradeService::class)->quoteBuy($this->btc, $this->ngn, 650000);

        $this->assertEquals('0.01000000', $quote['crypto_amount']);
    }

    public function test_sell_quote_uses_the_active_buy_rate(): void
    {
        $admin = User::factory()->create();
        app(RateService::class)->setRate($this->btc->id, $this->ngn->id, 64000000, 65000000, 24, $admin);

        $quote = app(TradeService::class)->quoteSell($this->btc, $this->ngn, 0.01);

        $this->assertEquals('640000.00', $quote['fiat_amount']);
    }

    public function test_admin_marking_a_buy_transaction_paid_credits_the_users_crypto_wallet(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        app(RateService::class)->setRate($this->btc->id, $this->ngn->id, 64000000, 65000000, 24, $admin);

        $transaction = app(TradeService::class)->initiateBuy($user, $this->btc, $this->ngn, 650000, 'bank_transfer');
        $this->assertEquals('pending', $transaction->status);

        app(TradeService::class)->completeBuy($transaction, $admin);

        $wallet = app(WalletService::class)->getOrCreateWallet($user, 'crypto', 'BTC');
        $this->assertEquals('0.01000000', $wallet->balance);

        // Completing a buy must never create a second Transaction row for
        // the same order — the original pending "buy" row IS the ledger
        // entry once it flips to completed.
        $this->assertSame(1, Transaction::where('user_id', $user->id)->count());
    }

    public function test_completing_a_sell_does_not_create_a_duplicate_transaction_row(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        app(RateService::class)->setRate($this->btc->id, $this->usd->id, 64000, 65000, 24, $admin);

        $transaction = app(TradeService::class)->initiateSell($user, $this->btc, $this->usd, 0.1, 'bc1qplatformaddress');
        app(TradeService::class)->confirmSell($transaction, $admin);

        $wallet = app(WalletService::class)->getOrCreateWallet($user, 'fiat', 'USD');
        $this->assertEquals('6400.00000000', $wallet->balance);
        $this->assertSame(1, Transaction::where('user_id', $user->id)->count());
    }

    public function test_submitting_the_same_buy_order_twice_quickly_does_not_create_two_orders(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        app(RateService::class)->setRate($this->btc->id, $this->ngn->id, 64000000, 65000000, 24, $admin);

        $first = app(TradeService::class)->initiateBuy($user, $this->btc, $this->ngn, 650000, 'bank_transfer');
        $second = app(TradeService::class)->initiateBuy($user, $this->btc, $this->ngn, 650000, 'bank_transfer');

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Transaction::where('user_id', $user->id)->where('type', 'buy')->count());
    }

    public function test_submitting_the_same_sell_order_twice_quickly_does_not_create_two_orders(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        app(RateService::class)->setRate($this->btc->id, $this->usd->id, 64000, 65000, 24, $admin);

        $first = app(TradeService::class)->initiateSell($user, $this->btc, $this->usd, 0.1, 'bc1qplatformaddress');
        $second = app(TradeService::class)->initiateSell($user, $this->btc, $this->usd, 0.1, 'bc1qplatformaddress');

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Transaction::where('user_id', $user->id)->where('type', 'sell')->count());
    }

    public function test_a_second_buy_order_after_the_duplicate_window_passes_is_allowed(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        app(RateService::class)->setRate($this->btc->id, $this->ngn->id, 64000000, 65000000, 24, $admin);

        $first = app(TradeService::class)->initiateBuy($user, $this->btc, $this->ngn, 650000, 'bank_transfer');
        $this->travel(21)->seconds();
        $second = app(TradeService::class)->initiateBuy($user, $this->btc, $this->ngn, 650000, 'bank_transfer');

        $this->assertNotSame($first->id, $second->id);
        $this->assertSame(2, Transaction::where('user_id', $user->id)->where('type', 'buy')->count());
    }

    public function test_swap_converts_between_two_crypto_assets_using_usd_cross_rate(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();

        app(RateService::class)->setRate($this->btc->id, $this->usd->id, 64000, 65000, 24, $admin);
        app(RateService::class)->setRate($this->eth->id, $this->usd->id, 3100, 3200, 24, $admin);

        $wallets = app(WalletService::class);
        $btcWallet = $wallets->getOrCreateWallet($user, 'crypto', 'BTC');
        $wallets->credit($btcWallet, '1', [
            'type' => 'deposit', 'currency_code' => 'BTC', 'status' => 'completed', 'reference' => 'TEST-DEP-1',
        ]);

        $quote = app(TradeService::class)->quoteSwap($user, $this->btc, $this->eth, 1.0);
        $transaction = app(TradeService::class)->executeSwap($user, $quote['token']);

        $this->assertEquals('completed', $transaction->status);

        $ethWallet = $wallets->getOrCreateWallet($user, 'crypto', 'ETH');
        $this->assertEquals('20.00000000', $ethWallet->balance);

        $btcWallet->refresh();
        $this->assertEquals('0.00000000', $btcWallet->balance);
    }

    public function test_swap_fails_with_insufficient_balance(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();

        app(RateService::class)->setRate($this->btc->id, $this->usd->id, 64000, 65000, 24, $admin);
        app(RateService::class)->setRate($this->eth->id, $this->usd->id, 3100, 3200, 24, $admin);

        $quote = app(TradeService::class)->quoteSwap($user, $this->btc, $this->eth, 1.0);

        $this->expectException(\RuntimeException::class);
        app(TradeService::class)->executeSwap($user, $quote['token']);
    }

    public function test_disabled_feature_shows_coming_soon_page(): void
    {
        SystemSetting::set(Features::BUY, false, 'boolean', 'features');

        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/buy');

        $response->assertOk();
        $response->assertSee('Coming Soon');
    }

    public function test_unverified_user_cannot_exceed_daily_trade_limit(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['daily_trade_limit' => 10, 'kyc_status' => 'unverified']);
        app(RateService::class)->setRate($this->btc->id, $this->usd->id, 64000, 65000, 24, $admin);

        $this->expectException(\RuntimeException::class);
        app(TradeService::class)->initiateBuy($user, $this->btc, $this->usd, 50, 'bank_transfer');
    }
}
