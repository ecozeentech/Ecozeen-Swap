<?php

namespace Tests\Feature;

use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Models\UserTrustedIp;
use App\Services\RateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyReceivingAddressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUpBuyFixtures(): array
    {
        $admin = User::factory()->create();
        $btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $usd = FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);
        app(RateService::class)->setRate($btc->id, $usd->id, 64000, 65000, 24, $admin);

        PaymentGateway::create([
            'name' => 'Bank Transfer', 'slug' => 'bank_transfer', 'is_active' => true, 'credentials' => [], 'metadata' => [],
        ]);

        return [$btc, $usd];
    }

    public function test_buy_page_shows_a_receiving_wallet_address_field(): void
    {
        [$btc, $usd] = $this->setUpBuyFixtures();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('buy.index'));

        $response->assertOk();
        $response->assertSee('Receiving Wallet Address', false);
        $response->assertSee('name="receiving_wallet_address"', false);
    }

    public function test_user_can_buy_and_specify_an_external_receiving_address(): void
    {
        [$btc, $usd] = $this->setUpBuyFixtures();
        $user = User::factory()->create();
        UserTrustedIp::create(['user_id' => $user->id, 'ip_address' => '127.0.0.1', 'verified_at' => now()]);

        $response = $this->actingAs($user)->post(route('buy.store'), [
            'crypto_asset_id' => $btc->id,
            'fiat_currency_id' => $usd->id,
            'fiat_amount' => 1000,
            'payment_method' => 'bank_transfer',
            'receiving_wallet_address' => 'bc1qexternaladdressexample',
        ]);

        $response->assertRedirect();

        $transaction = $user->transactions()->where('type', 'buy')->latest()->first();
        $this->assertNotNull($transaction);
        $this->assertSame('bc1qexternaladdressexample', $transaction->metadata['receiving_wallet_address']);
    }

    public function test_buying_without_a_receiving_address_still_works_as_before(): void
    {
        [$btc, $usd] = $this->setUpBuyFixtures();
        $user = User::factory()->create();
        UserTrustedIp::create(['user_id' => $user->id, 'ip_address' => '127.0.0.1', 'verified_at' => now()]);

        $response = $this->actingAs($user)->post(route('buy.store'), [
            'crypto_asset_id' => $btc->id,
            'fiat_currency_id' => $usd->id,
            'fiat_amount' => 1000,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertRedirect();

        $transaction = $user->transactions()->where('type', 'buy')->latest()->first();
        $this->assertNotNull($transaction);
        $this->assertArrayNotHasKey('receiving_wallet_address', $transaction->metadata);
    }

    public function test_admin_sees_the_receiving_address_prominently_on_the_transaction_page(): void
    {
        [$btc, $usd] = $this->setUpBuyFixtures();
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        UserTrustedIp::create(['user_id' => $user->id, 'ip_address' => '127.0.0.1', 'verified_at' => now()]);

        $this->actingAs($user)->post(route('buy.store'), [
            'crypto_asset_id' => $btc->id,
            'fiat_currency_id' => $usd->id,
            'fiat_amount' => 1000,
            'payment_method' => 'bank_transfer',
            'receiving_wallet_address' => 'bc1qexternaladdressexample',
        ]);

        $transaction = $user->transactions()->where('type', 'buy')->latest()->first();

        $response = $this->actingAs($admin)->get(route('admin.transactions.show', $transaction));

        $response->assertOk();
        $response->assertSee('Deliver externally to');
        $response->assertSee('bc1qexternaladdressexample');
    }
}
