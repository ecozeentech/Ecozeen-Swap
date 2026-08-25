<?php

namespace Tests\Feature;

use App\Models\CryptoAsset;
use App\Models\CryptoWallet;
use App\Models\FiatCurrency;
use App\Models\User;
use App\Models\UserTrustedIp;
use App\Services\RateService;
use App\Services\TradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountAndCryptoWalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_edit_and_set_default_bank_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('bank-accounts.store'), [
            'bank_name' => 'GTBank',
            'account_name' => 'Jane Doe',
            'account_number' => '0123456789',
        ])->assertRedirect();

        $account = $user->bankAccounts()->first();
        $this->assertNotNull($account);
        $this->assertTrue($account->is_default); // first account becomes default automatically

        $second = $user->bankAccounts()->create([
            'bank_name' => 'Access Bank', 'account_name' => 'Jane Doe', 'account_number' => '9876543210',
        ]);

        $this->actingAs($user)->post(route('bank-accounts.set-default', $second))->assertRedirect();

        $this->assertFalse($account->refresh()->is_default);
        $this->assertTrue($second->refresh()->is_default);
    }

    public function test_user_cannot_manage_another_users_bank_account(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $account = $other->bankAccounts()->create([
            'bank_name' => 'GTBank', 'account_name' => 'Other', 'account_number' => '111',
        ]);

        $this->actingAs($user)->delete(route('bank-accounts.destroy', $account))->assertForbidden();
    }

    public function test_admin_can_manage_crypto_wallet_addresses(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $crypto = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.crypto-wallets.store'), [
            'crypto_asset_id' => $crypto->id,
            'wallet_address' => 'bc1qtest',
            'label' => 'Hot Wallet',
            'is_active' => 1,
            'is_default' => 1,
        ])->assertRedirect();

        $wallet = CryptoWallet::query()->where('wallet_address', 'bc1qtest')->first();
        $this->assertNotNull($wallet);
        $this->assertTrue($wallet->is_default);

        $second = CryptoWallet::create([
            'crypto_asset_id' => $crypto->id, 'wallet_address' => 'bc1qsecond', 'label' => 'Cold Storage', 'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.crypto-wallets.set-default', $second))->assertRedirect();

        $this->assertFalse($wallet->refresh()->is_default);
        $this->assertTrue($second->refresh()->is_default);
    }

    public function test_deposit_page_shows_admin_configured_wallet_address(): void
    {
        $user = User::factory()->create();
        $crypto = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        CryptoWallet::create(['crypto_asset_id' => $crypto->id, 'wallet_address' => 'bc1qplatformaddress', 'is_active' => true, 'is_default' => true]);

        $response = $this->actingAs($user)->get(route('wallet.deposit', $crypto));

        $response->assertOk();
        $response->assertSee('bc1qplatformaddress');
    }

    public function test_sell_requires_a_bank_account_and_uses_platform_wallet_address(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('user');

        $btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $usd = FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);
        CryptoWallet::create(['crypto_asset_id' => $btc->id, 'wallet_address' => 'bc1qplatform', 'is_active' => true, 'is_default' => true]);
        app(RateService::class)->setRate($btc->id, $usd->id, 64000, 65000, 24, $admin);

        $bankAccount = $user->bankAccounts()->create([
            'bank_name' => 'GTBank', 'account_name' => 'Jane Doe', 'account_number' => '0123456789', 'is_default' => true,
        ]);

        UserTrustedIp::create(['user_id' => $user->id, 'ip_address' => '127.0.0.1', 'verified_at' => now()]);

        $response = $this->actingAs($user)->post(route('sell.store'), [
            'crypto_asset_id' => $btc->id,
            'fiat_currency_id' => $usd->id,
            'crypto_amount' => 0.01,
            'bank_account_id' => $bankAccount->id,
        ]);

        $response->assertRedirect();

        $transaction = $user->transactions()->where('type', 'sell')->latest()->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('bc1qplatform', $transaction->metadata['deposit_address']);
        $this->assertEquals($bankAccount->id, $transaction->metadata['settlement_bank_account_id']);
    }

    public function test_kyc_never_blocks_trading_only_lowers_limit(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['kyc_status' => 'unverified', 'daily_trade_limit' => 100000]);

        $btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $usd = FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);
        app(RateService::class)->setRate($btc->id, $usd->id, 64000, 65000, 24, $admin);

        $transaction = app(TradeService::class)->initiateBuy($user, $btc, $usd, 100, 'bank_transfer');

        $this->assertEquals('pending', $transaction->status);
    }
}
