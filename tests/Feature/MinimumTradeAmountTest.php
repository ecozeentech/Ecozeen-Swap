<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\CryptoAsset;
use App\Models\CryptoWallet;
use App\Models\FiatCurrency;
use App\Models\PaymentGateway;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserTrustedIp;
use App\Services\RateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MinimumTradeAmountTest extends TestCase
{
    use RefreshDatabase;

    protected CryptoAsset $btc;

    protected FiatCurrency $usd;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create();
        $this->btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $this->usd = FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);
        app(RateService::class)->setRate($this->btc->id, $this->usd->id, 64000, 65000, 24, $admin);

        PaymentGateway::create(['name' => 'Bank Transfer', 'slug' => 'bank_transfer', 'is_active' => true, 'credentials' => [], 'metadata' => []]);
        CryptoWallet::create(['crypto_asset_id' => $this->btc->id, 'wallet_address' => 'bc1qplatform', 'is_active' => true, 'is_default' => true]);

        $this->user = User::factory()->create();
        UserTrustedIp::create(['user_id' => $this->user->id, 'ip_address' => '127.0.0.1', 'verified_at' => now()]);
        BankAccount::create(['user_id' => $this->user->id, 'bank_name' => 'Test Bank', 'account_name' => 'Test', 'account_number' => '0000000000', 'is_default' => true]);
    }

    public function test_buy_below_the_admin_minimum_is_rejected(): void
    {
        SystemSetting::set('min_buy_amount_usd', 50, 'string', 'trading');

        $response = $this->actingAs($this->user)->post(route('buy.store'), [
            'crypto_asset_id' => $this->btc->id,
            'fiat_currency_id' => $this->usd->id,
            'fiat_amount' => 20,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertSessionHasErrors('fiat_amount');
        $this->assertSame(0, Transaction::where('type', 'buy')->count());
    }

    public function test_buy_at_or_above_the_admin_minimum_succeeds(): void
    {
        SystemSetting::set('min_buy_amount_usd', 50, 'string', 'trading');

        $response = $this->actingAs($this->user)->post(route('buy.store'), [
            'crypto_asset_id' => $this->btc->id,
            'fiat_currency_id' => $this->usd->id,
            'fiat_amount' => 100,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(1, Transaction::where('type', 'buy')->count());
    }

    public function test_sell_below_the_admin_minimum_is_rejected(): void
    {
        SystemSetting::set('min_sell_amount_usd', 50, 'string', 'trading');

        // 0.0005 BTC * 64000 buy rate = $32, below the $50 minimum.
        $response = $this->actingAs($this->user)->post(route('sell.store'), [
            'crypto_asset_id' => $this->btc->id,
            'fiat_currency_id' => $this->usd->id,
            'crypto_amount' => 0.0005,
            'bank_account_id' => $this->user->bankAccounts()->first()->id,
        ]);

        $response->assertSessionHasErrors('crypto_amount');
        $this->assertSame(0, Transaction::where('type', 'sell')->count());
    }

    public function test_sell_at_or_above_the_admin_minimum_succeeds(): void
    {
        SystemSetting::set('min_sell_amount_usd', 50, 'string', 'trading');

        // 0.01 BTC * 64000 = $640, above the $50 minimum.
        $response = $this->actingAs($this->user)->post(route('sell.store'), [
            'crypto_asset_id' => $this->btc->id,
            'fiat_currency_id' => $this->usd->id,
            'crypto_amount' => 0.01,
            'bank_account_id' => $this->user->bankAccounts()->first()->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(1, Transaction::where('type', 'sell')->count());
    }

    public function test_a_zero_minimum_disables_the_check(): void
    {
        SystemSetting::set('min_buy_amount_usd', 0, 'string', 'trading');

        $response = $this->actingAs($this->user)->post(route('buy.store'), [
            'crypto_asset_id' => $this->btc->id,
            'fiat_currency_id' => $this->usd->id,
            'fiat_amount' => 1,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_admin_can_update_minimum_trade_amounts(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'support_widget_type' => 'none',
            'giftcard_buyback_rate' => 75,
            'default_display_currency' => 'USD',
            'min_buy_amount_usd' => 25,
            'min_sell_amount_usd' => 15,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('25', SystemSetting::get('min_buy_amount_usd'));
        $this->assertSame('15', SystemSetting::get('min_sell_amount_usd'));
    }

    public function test_buy_page_shows_the_minimum_amount(): void
    {
        SystemSetting::set('min_buy_amount_usd', 25, 'string', 'trading');

        $response = $this->actingAs($this->user)->get(route('buy.index'));

        $response->assertOk();
        $response->assertSee('$25.00');
    }
}
