<?php

namespace Tests\Feature;

use App\Http\Controllers\WalletController;
use App\Models\BankAccount;
use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Models\GiftCard;
use App\Models\ReferralCommission;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserTrustedIp;
use App\Models\Wallet;
use App\Services\PaymentSettlement;
use App\Services\RateService;
use App\Services\ReferralService;
use App\Services\TradeService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class WalletLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function rate(CryptoAsset $crypto, FiatCurrency $fiat): void
    {
        $admin = User::factory()->create();
        app(RateService::class)->setRate($crypto->id, $fiat->id, 1500, 1600, 24, $admin);
    }

    public function test_a_deposit_is_credited_once_when_callback_and_settlement_both_run(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => '25000',
            'currency_code' => 'NGN',
            'status' => 'pending',
            'reference' => Transaction::generateReference('DEP'),
        ]);

        app(PaymentSettlement::class)->settle($transaction);
        app(WalletController::class)->creditDeposit($transaction->fresh());
        app(PaymentSettlement::class)->settle($transaction->fresh());

        $wallet = Wallet::query()->where('user_id', $user->id)->where('currency_type', 'fiat')->where('currency_code', 'NGN')->first();

        $this->assertNotNull($wallet);
        $this->assertEquals('25000.00000000', $wallet->balance);
        $this->assertSame(1, Wallet::query()->where('user_id', $user->id)->where('currency_code', 'NGN')->count());
        $this->assertSame('completed', $transaction->fresh()->status);
    }

    public function test_settling_a_buy_credits_crypto_once_and_does_not_invent_a_fiat_wallet(): void
    {
        $user = User::factory()->create(['kyc_status' => 'verified']);
        $btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $ngn = FiatCurrency::create(['code' => 'NGN', 'name' => 'Naira', 'symbol' => '₦', 'exchange_rate_to_usd' => 0.00062, 'is_active' => true]);
        $this->rate($btc, $ngn);

        Wallet::create([
            'user_id' => $user->id, 'currency_type' => 'fiat', 'currency_code' => 'NGN',
            'balance' => '5000', 'reserved_balance' => '0',
        ]);

        $transaction = app(TradeService::class)->initiateBuy($user, $btc, $ngn, 1600, 'paystack');

        app(WalletController::class)->creditDeposit($transaction);
        app(PaymentSettlement::class)->settle($transaction->fresh());

        $crypto = Wallet::query()->where('user_id', $user->id)->where('currency_type', 'crypto')->where('currency_code', 'BTC')->first();
        $naira = Wallet::query()->where('user_id', $user->id)->where('currency_type', 'fiat')->where('currency_code', 'NGN')->first();

        $this->assertEquals('1.00000000', $crypto->balance);
        $this->assertEquals('5000.00000000', $naira->balance);
        $this->assertSame(0, Wallet::query()->where('user_id', $user->id)->where('currency_type', 'fiat')->where('currency_code', 'BTC')->count());
        $this->assertSame(1, Transaction::query()->where('user_id', $user->id)->where('type', 'buy')->count());
    }

    public function test_a_buy_sent_to_an_external_address_does_not_stay_on_the_wallet(): void
    {
        $user = User::factory()->create(['kyc_status' => 'verified']);
        $usdt = CryptoAsset::create(['name' => 'Tether', 'symbol' => 'USDT', 'decimal_places' => 2, 'is_active' => true]);
        $ngn = FiatCurrency::create(['code' => 'NGN', 'name' => 'Naira', 'symbol' => '₦', 'exchange_rate_to_usd' => 0.00062, 'is_active' => true]);
        $this->rate($usdt, $ngn);

        $transaction = app(TradeService::class)->initiateBuy($user, $usdt, $ngn, 160000, 'paystack', [
            'receiving_wallet_address' => 'TExternalUsdtAddress',
        ]);

        app(PaymentSettlement::class)->settle($transaction);

        $crypto = Wallet::query()->where('user_id', $user->id)->where('currency_type', 'crypto')->where('currency_code', 'USDT')->first();

        $this->assertEquals('0.00000000', $crypto->balance);
        $this->assertSame('completed', $transaction->fresh()->status);
        $this->assertSame('0.00000000', $crypto->availableBalance());
    }

    public function test_a_second_withdrawal_of_the_same_balance_is_refused(): void
    {
        $user = User::factory()->create();
        UserTrustedIp::create(['user_id' => $user->id, 'ip_address' => '127.0.0.1', 'verified_at' => now()]);
        Wallet::create([
            'user_id' => $user->id, 'currency_type' => 'crypto', 'currency_code' => 'USDT',
            'balance' => '100', 'reserved_balance' => '0',
        ]);

        $payload = [
            'currency_type' => 'crypto',
            'currency_code' => 'USDT',
            'amount' => 100,
            'destination' => 'TExternalAddress',
        ];

        $first = $this->actingAs($user)->post(route('wallet.withdraw.store'), $payload);
        $first->assertRedirect();
        $first->assertSessionHasNoErrors();

        $second = $this->actingAs($user)->post(route('wallet.withdraw.store'), $payload);
        $second->assertSessionHasErrors('amount');

        $wallet = Wallet::query()->where('user_id', $user->id)->where('currency_code', 'USDT')->first();
        $this->assertEquals('100.00000000', $wallet->balance);
        $this->assertEquals('100.00000000', $wallet->reserved_balance);
        $this->assertEquals('0.00000000', $wallet->availableBalance());
        $this->assertSame(1, Transaction::query()->where('user_id', $user->id)->where('type', 'withdrawal')->count());
    }

    public function test_approving_a_withdrawal_removes_it_from_the_available_balance(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        $wallet = Wallet::create([
            'user_id' => $user->id, 'currency_type' => 'fiat', 'currency_code' => 'NGN',
            'balance' => '8000', 'reserved_balance' => '3000',
        ]);
        $transaction = Transaction::create([
            'user_id' => $user->id, 'wallet_id' => $wallet->id, 'type' => 'withdrawal',
            'amount' => '3000', 'currency_code' => 'NGN', 'status' => 'pending',
            'reference' => Transaction::generateReference('WD'),
        ]);

        $this->actingAs($admin)->post(route('admin.transactions.approve-withdrawal', $transaction))->assertSessionHasNoErrors();
        $again = $this->actingAs($admin)->post(route('admin.transactions.approve-withdrawal', $transaction->fresh()));
        $again->assertSessionHasErrors('transaction');

        $wallet->refresh();
        $this->assertEquals('5000.00000000', $wallet->balance);
        $this->assertEquals('0.00000000', $wallet->reserved_balance);
        $this->assertEquals('5000.00000000', $wallet->availableBalance());
    }

    public function test_approving_a_withdrawal_larger_than_the_balance_is_refused(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        $wallet = Wallet::create([
            'user_id' => $user->id, 'currency_type' => 'fiat', 'currency_code' => 'NGN',
            'balance' => '50', 'reserved_balance' => '100',
        ]);
        $transaction = Transaction::create([
            'user_id' => $user->id, 'wallet_id' => $wallet->id, 'type' => 'withdrawal',
            'amount' => '100', 'currency_code' => 'NGN', 'status' => 'pending',
            'reference' => Transaction::generateReference('WD'),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.transactions.approve-withdrawal', $transaction));

        $response->assertSessionHasErrors('transaction');
        $wallet->refresh();
        $this->assertEquals('50.00000000', $wallet->balance);
        $this->assertEquals('100.00000000', $wallet->reserved_balance);
        $this->assertSame('pending', $transaction->fresh()->status);
        $this->assertEquals('0.00000000', $wallet->availableBalance());
    }

    public function test_selling_coins_already_in_the_wallet_holds_them_until_the_sale_is_confirmed(): void
    {
        $user = User::factory()->create(['kyc_status' => 'verified']);
        UserTrustedIp::create(['user_id' => $user->id, 'ip_address' => '127.0.0.1', 'verified_at' => now()]);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $usdt = CryptoAsset::create(['name' => 'Tether', 'symbol' => 'USDT', 'decimal_places' => 2, 'is_active' => true]);
        $ngn = FiatCurrency::create(['code' => 'NGN', 'name' => 'Naira', 'symbol' => '₦', 'exchange_rate_to_usd' => 0.00062, 'is_active' => true]);
        $this->rate($usdt, $ngn);

        $crypto = app(WalletService::class)->getOrCreateWallet($user, 'crypto', 'USDT');
        app(WalletService::class)->credit($crypto, '100', [
            'type' => 'deposit', 'currency_code' => 'USDT', 'status' => 'completed', 'reference' => 'TEST-USDT',
        ]);

        $sell = app(TradeService::class)->initiateSell($user, $usdt, $ngn, 40, 'platform-usdt-address');

        $crypto->refresh();
        $this->assertSame('wallet', $sell->metadata['funding_source']);
        $this->assertEquals('100.00000000', $crypto->balance);
        $this->assertEquals('40.00000000', $crypto->reserved_balance);
        $this->assertEquals('60.00000000', $crypto->availableBalance());

        $withdraw = $this->actingAs($user)->post(route('wallet.withdraw.store'), [
            'currency_type' => 'crypto',
            'currency_code' => 'USDT',
            'amount' => 100,
            'destination' => 'TExternalAddress',
        ]);
        $withdraw->assertSessionHasErrors('amount');

        $page = $this->actingAs($user)->get(route('sell.show', $sell));
        $page->assertOk();
        $page->assertSee('is held');
        $page->assertDontSee('platform-usdt-address');

        $adminPage = $this->actingAs($admin)->get(route('admin.transactions.show', $sell));
        $adminPage->assertOk();
        $adminPage->assertSee('already held');

        app(TradeService::class)->confirmSell($sell, $admin);

        $crypto->refresh();
        $naira = Wallet::query()->where('user_id', $user->id)->where('currency_type', 'fiat')->where('currency_code', 'NGN')->first();
        $this->assertEquals('60.00000000', $crypto->balance);
        $this->assertEquals('0.00000000', $crypto->reserved_balance);
        $this->assertEquals('60.00000000', $crypto->availableBalance());
        $this->assertEquals('60000.00000000', $naira->balance);
    }

    public function test_rejecting_a_wallet_funded_sell_releases_the_hold(): void
    {
        $user = User::factory()->create(['kyc_status' => 'verified']);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $usdt = CryptoAsset::create(['name' => 'Tether', 'symbol' => 'USDT', 'decimal_places' => 2, 'is_active' => true]);
        $ngn = FiatCurrency::create(['code' => 'NGN', 'name' => 'Naira', 'symbol' => '₦', 'exchange_rate_to_usd' => 0.00062, 'is_active' => true]);
        $this->rate($usdt, $ngn);

        $crypto = app(WalletService::class)->getOrCreateWallet($user, 'crypto', 'USDT');
        app(WalletService::class)->credit($crypto, '100', [
            'type' => 'deposit', 'currency_code' => 'USDT', 'status' => 'completed', 'reference' => 'TEST-USDT-2',
        ]);

        $sell = app(TradeService::class)->initiateSell($user, $usdt, $ngn, 40, 'platform-usdt-address');

        $this->actingAs($admin)->post(route('admin.transactions.reject', $sell), ['reason' => 'Not received']);

        $crypto->refresh();
        $this->assertEquals('100.00000000', $crypto->balance);
        $this->assertEquals('0.00000000', $crypto->reserved_balance);
        $this->assertSame('failed', $sell->fresh()->status);
        $naira = Wallet::query()->where('user_id', $user->id)->where('currency_type', 'fiat')->where('currency_code', 'NGN')->first();
        $this->assertEquals('0.00000000', $naira->balance);
    }

    public function test_an_external_sell_from_an_empty_wallet_still_pays_fiat_without_going_negative(): void
    {
        $user = User::factory()->create(['kyc_status' => 'verified']);
        $admin = User::factory()->create();
        $usdt = CryptoAsset::create(['name' => 'Tether', 'symbol' => 'USDT', 'decimal_places' => 2, 'is_active' => true]);
        $ngn = FiatCurrency::create(['code' => 'NGN', 'name' => 'Naira', 'symbol' => '₦', 'exchange_rate_to_usd' => 0.00062, 'is_active' => true]);
        $this->rate($usdt, $ngn);

        $sell = app(TradeService::class)->initiateSell($user, $usdt, $ngn, 10, 'platform-usdt-address');
        $this->assertSame('external', $sell->metadata['funding_source']);

        app(TradeService::class)->confirmSell($sell, $admin);

        $crypto = Wallet::query()->where('user_id', $user->id)->where('currency_type', 'crypto')->where('currency_code', 'USDT')->first();
        $naira = Wallet::query()->where('user_id', $user->id)->where('currency_type', 'fiat')->where('currency_code', 'NGN')->first();

        $this->assertEquals('0.00000000', $crypto->balance);
        $this->assertEquals('15000.00000000', $naira->balance);
    }

    public function test_a_sell_larger_than_coins_already_held_is_rejected(): void
    {
        $user = User::factory()->create(['kyc_status' => 'verified']);
        $usdt = CryptoAsset::create(['name' => 'Tether', 'symbol' => 'USDT', 'decimal_places' => 2, 'is_active' => true]);
        $ngn = FiatCurrency::create(['code' => 'NGN', 'name' => 'Naira', 'symbol' => '₦', 'exchange_rate_to_usd' => 0.00062, 'is_active' => true]);
        $this->rate($usdt, $ngn);

        $crypto = app(WalletService::class)->getOrCreateWallet($user, 'crypto', 'USDT');
        app(WalletService::class)->credit($crypto, '40', [
            'type' => 'deposit', 'currency_code' => 'USDT', 'status' => 'completed', 'reference' => 'TEST-USDT-3',
        ]);

        try {
            app(TradeService::class)->initiateSell($user, $usdt, $ngn, 100, 'platform-usdt-address');
            $this->fail('An oversized wallet sell should have been rejected.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('available', $e->getMessage());
        }

        $crypto->refresh();
        $this->assertEquals('40.00000000', $crypto->balance);
        $this->assertEquals('0.00000000', $crypto->reserved_balance);
        $this->assertSame(0, Transaction::query()->where('user_id', $user->id)->where('type', 'sell')->count());
    }

    public function test_an_older_sell_without_a_funding_source_does_not_debit_the_wallet(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $crypto = Wallet::create([
            'user_id' => $user->id, 'currency_type' => 'crypto', 'currency_code' => 'USDT',
            'balance' => '25', 'reserved_balance' => '0',
        ]);
        $sell = Transaction::create([
            'user_id' => $user->id,
            'type' => 'sell',
            'amount' => '1000',
            'currency_code' => 'NGN',
            'status' => 'pending',
            'reference' => Transaction::generateReference('SELL'),
            'metadata' => ['crypto_amount' => 10, 'crypto_symbol' => 'USDT'],
        ]);

        app(TradeService::class)->confirmSell($sell, $admin);

        $crypto->refresh();
        $this->assertEquals('25.00000000', $crypto->balance);
        $naira = Wallet::query()->where('user_id', $user->id)->where('currency_code', 'NGN')->first();
        $this->assertEquals('1000.00000000', $naira->balance);
    }

    public function test_a_swap_quote_cannot_be_executed_twice(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['kyc_status' => 'verified']);
        $btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $eth = CryptoAsset::create(['name' => 'Ethereum', 'symbol' => 'ETH', 'decimal_places' => 8, 'is_active' => true]);
        $usd = FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);
        app(RateService::class)->setRate($btc->id, $usd->id, 64000, 65000, 24, $admin);
        app(RateService::class)->setRate($eth->id, $usd->id, 3100, 3200, 24, $admin);

        $btcWallet = app(WalletService::class)->getOrCreateWallet($user, 'crypto', 'BTC');
        app(WalletService::class)->credit($btcWallet, '1', [
            'type' => 'deposit', 'currency_code' => 'BTC', 'status' => 'completed', 'reference' => 'TEST-BTC',
        ]);

        $quote = app(TradeService::class)->quoteSwap($user, $btc, $eth, 1.0);
        app(TradeService::class)->executeSwap($user, $quote['token']);

        try {
            app(TradeService::class)->executeSwap($user, $quote['token']);
            $this->fail('A consumed swap quote should not run again.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('expired', $e->getMessage());
        }

        $btcWallet->refresh();
        $eth = Wallet::query()->where('user_id', $user->id)->where('currency_code', 'ETH')->first();
        $this->assertEquals('0.00000000', $btcWallet->balance);
        $this->assertEquals('20.00000000', $eth->balance);
    }

    public function test_approving_a_gift_card_twice_credits_the_payout_once(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        $giftCard = GiftCard::create([
            'user_id' => $user->id, 'card_type' => 'Amazon', 'card_number_hashed' => 'hash',
            'pin' => '1234', 'face_value' => 100, 'face_value_currency' => 'USD',
            'rate_applied' => 0.9, 'selling_price' => 90, 'payout_currency' => 'USD', 'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.giftcards.approve', $giftCard))->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.giftcards.approve', $giftCard->fresh()))->assertSessionHasErrors('gift_card');

        $wallet = Wallet::query()->where('user_id', $user->id)->where('currency_type', 'fiat')->where('currency_code', 'USD')->first();
        $this->assertEquals('90.00000000', $wallet->balance);
        $this->assertSame(1, Transaction::query()->where('user_id', $user->id)->where('type', 'giftcard')->count());
        $this->assertSame('paid', $giftCard->fresh()->status);
    }

    public function test_a_second_referral_withdrawal_cannot_spend_the_same_commission(): void
    {
        $user = User::factory()->create();
        $bankAccount = BankAccount::create([
            'user_id' => $user->id, 'bank_name' => 'Test Bank', 'account_name' => 'Test',
            'account_number' => '1234567890', 'is_default' => true,
        ]);
        ReferralCommission::create(['referrer_id' => $user->id, 'type' => 'manual_adjustment', 'amount' => 50]);

        app(ReferralService::class)->requestWithdrawal($user, 50, $bankAccount);

        $this->expectException(RuntimeException::class);
        app(ReferralService::class)->requestWithdrawal($user, 50, $bankAccount);
    }

    public function test_a_repeated_wallet_funded_sell_does_not_hold_the_coins_twice(): void
    {
        $user = User::factory()->create(['kyc_status' => 'verified']);
        $usdt = CryptoAsset::create(['name' => 'Tether', 'symbol' => 'USDT', 'decimal_places' => 2, 'is_active' => true]);
        $ngn = FiatCurrency::create(['code' => 'NGN', 'name' => 'Naira', 'symbol' => '₦', 'exchange_rate_to_usd' => 0.00062, 'is_active' => true]);
        $this->rate($usdt, $ngn);

        $crypto = app(WalletService::class)->getOrCreateWallet($user, 'crypto', 'USDT');
        app(WalletService::class)->credit($crypto, '100', [
            'type' => 'deposit', 'currency_code' => 'USDT', 'status' => 'completed', 'reference' => 'TEST-USDT-4',
        ]);

        $first = app(TradeService::class)->initiateSell($user, $usdt, $ngn, 40, 'platform-usdt-address');
        $second = app(TradeService::class)->initiateSell($user, $usdt, $ngn, 40, 'platform-usdt-address');

        $this->assertSame($first->id, $second->id);
        $crypto->refresh();
        $this->assertEquals('40.00000000', $crypto->reserved_balance);
        $this->assertSame(1, Transaction::query()->where('user_id', $user->id)->where('type', 'sell')->count());
    }
}
