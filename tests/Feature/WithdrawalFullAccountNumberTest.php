<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\CryptoAsset;
use App\Models\CryptoWallet;
use App\Models\FiatCurrency;
use App\Models\ReferralWithdrawal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserTrustedIp;
use App\Models\Wallet;
use App\Services\RateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalFullAccountNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_requesting_a_fiat_withdrawal_stores_the_full_account_number(): void
    {
        $user = User::factory()->create();
        UserTrustedIp::create(['user_id' => $user->id, 'ip_address' => '127.0.0.1', 'verified_at' => now()]);
        $bankAccount = BankAccount::create([
            'user_id' => $user->id, 'bank_name' => 'First Bank', 'account_name' => 'Jane Doe',
            'account_number' => '0123456789', 'is_default' => true,
        ]);
        Wallet::create(['user_id' => $user->id, 'currency_type' => 'fiat', 'currency_code' => 'USD', 'balance' => 1000, 'reserved_balance' => 0]);

        $response = $this->actingAs($user)->post(route('wallet.withdraw.store'), [
            'currency_type' => 'fiat',
            'currency_code' => 'USD',
            'amount' => 100,
            'bank_account_id' => $bankAccount->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $transaction = Transaction::where('type', 'withdrawal')->latest()->first();
        $this->assertSame('0123456789', $transaction->metadata['account_number']);
    }

    public function test_admin_sees_the_full_account_number_on_the_withdrawal_order_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        $wallet = Wallet::create(['user_id' => $user->id, 'currency_type' => 'fiat', 'currency_code' => 'USD', 'balance' => 500, 'reserved_balance' => 100]);

        $transaction = Transaction::create([
            'user_id' => $user->id, 'wallet_id' => $wallet->id, 'type' => 'withdrawal', 'amount' => 100,
            'currency_code' => 'USD', 'status' => 'pending', 'reference' => Transaction::generateReference('WD'),
            'metadata' => [
                'bank_name' => 'First Bank',
                'account_name' => 'Jane Doe',
                'account_number' => '0123456789',
                'account_number_last4' => '6789',
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.transactions.show', $transaction));

        $response->assertOk();
        $response->assertSee('0123456789');
        $response->assertDontSee('****6789');
    }

    public function test_admin_sees_a_fallback_notice_for_older_orders_with_only_the_last_four_digits(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        $wallet = Wallet::create(['user_id' => $user->id, 'currency_type' => 'fiat', 'currency_code' => 'USD', 'balance' => 500, 'reserved_balance' => 100]);

        $transaction = Transaction::create([
            'user_id' => $user->id, 'wallet_id' => $wallet->id, 'type' => 'withdrawal', 'amount' => 100,
            'currency_code' => 'USD', 'status' => 'pending', 'reference' => Transaction::generateReference('WD'),
            'metadata' => [
                'bank_name' => 'First Bank',
                'account_name' => 'Jane Doe',
                'account_number_last4' => '6789',
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.transactions.show', $transaction));

        $response->assertOk();
        $response->assertSee('****6789');
    }

    public function test_sell_order_stores_the_full_settlement_account_number(): void
    {
        $admin = User::factory()->create();
        $btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $usd = FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);
        app(RateService::class)->setRate($btc->id, $usd->id, 64000, 65000, 24, $admin);
        CryptoWallet::create(['crypto_asset_id' => $btc->id, 'wallet_address' => 'bc1qplatform', 'is_active' => true, 'is_default' => true]);

        $user = User::factory()->create();
        UserTrustedIp::create(['user_id' => $user->id, 'ip_address' => '127.0.0.1', 'verified_at' => now()]);
        $bankAccount = BankAccount::create([
            'user_id' => $user->id, 'bank_name' => 'First Bank', 'account_name' => 'Jane Doe',
            'account_number' => '0123456789', 'is_default' => true,
        ]);

        $response = $this->actingAs($user)->post(route('sell.store'), [
            'crypto_asset_id' => $btc->id,
            'fiat_currency_id' => $usd->id,
            'crypto_amount' => 0.01,
            'bank_account_id' => $bankAccount->id,
        ]);

        $response->assertRedirect();
        $transaction = Transaction::where('type', 'sell')->latest()->first();
        $this->assertSame('0123456789', $transaction->metadata['settlement_account_number']);

        $adminViewer = User::factory()->create();
        $adminViewer->assignRole('super-admin');
        $this->actingAs($adminViewer)->get(route('admin.transactions.show', $transaction))->assertSee('0123456789');
    }

    public function test_admin_sees_the_full_account_number_for_a_referral_withdrawal(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        $bankAccount = $user->bankAccounts()->create(['bank_name' => 'First Bank', 'account_name' => 'Jane Doe', 'account_number' => '0123456789', 'is_default' => true]);

        $withdrawal = ReferralWithdrawal::create([
            'user_id' => $user->id, 'bank_account_id' => $bankAccount->id, 'amount' => 50,
            'status' => 'pending', 'reference' => ReferralWithdrawal::generateReference(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.referrals.withdrawals'));

        $response->assertOk();
        $response->assertSee('0123456789');
    }
}
