<?php

namespace Tests\Feature\Admin;

use App\Models\CryptoAsset;
use App\Models\DailyRate;
use App\Models\FiatCurrency;
use App\Models\GiftCardProduct;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        return $admin;
    }

    public function test_admin_can_create_edit_and_delete_a_crypto_asset(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.crypto.store'), [
            'name' => 'Litecoin',
            'symbol' => 'LTC',
            'decimal_places' => 8,
        ])->assertRedirect();

        $asset = CryptoAsset::query()->where('symbol', 'LTC')->first();
        $this->assertNotNull($asset);

        $this->actingAs($admin)->put(route('admin.crypto.update', $asset), [
            'name' => 'Litecoin Updated',
            'symbol' => 'LTC',
            'decimal_places' => 8,
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertEquals('Litecoin Updated', $asset->refresh()->name);

        $this->actingAs($admin)->delete(route('admin.crypto.destroy', $asset))->assertRedirect();
        $this->assertNull(CryptoAsset::find($asset->id));
    }

    public function test_crypto_asset_with_a_funded_wallet_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $asset = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'currency_type' => 'crypto', 'currency_code' => 'BTC', 'balance' => 1]);

        $this->actingAs($admin)->delete(route('admin.crypto.destroy', $asset))->assertSessionHasErrors('crypto');
        $this->assertNotNull(CryptoAsset::find($asset->id));
    }

    public function test_admin_can_delete_a_fiat_currency_and_a_daily_rate(): void
    {
        $admin = $this->admin();
        $fiat = FiatCurrency::create(['code' => 'GHS', 'name' => 'Cedi', 'symbol' => 'GH₵', 'exchange_rate_to_usd' => 0.07, 'is_active' => true]);
        $crypto = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);

        $rate = DailyRate::create([
            'crypto_asset_id' => $crypto->id,
            'fiat_currency_id' => $fiat->id,
            'buy_rate' => 100,
            'sell_rate' => 110,
            'starts_at' => now(),
            'expires_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->actingAs($admin)->delete(route('admin.rates.destroy', $rate))->assertRedirect();
        $this->assertNull(DailyRate::find($rate->id));

        $this->actingAs($admin)->delete(route('admin.fiat.destroy', $fiat))->assertRedirect();
        $this->assertNull(FiatCurrency::find($fiat->id));
    }

    public function test_admin_can_manage_gift_card_catalog_with_country_targeting(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.giftcard-products.store'), [
            'name' => 'Amazon Gift Card',
            'currency' => 'USD',
            'min_amount' => 5,
            'max_amount' => 500,
            'countries' => ['US', 'GB'],
            'is_active' => 1,
        ])->assertRedirect();

        $product = GiftCardProduct::query()->where('name', 'Amazon Gift Card')->first();
        $this->assertNotNull($product);
        $this->assertTrue($product->isAvailableIn('US'));
        $this->assertFalse($product->isAvailableIn('NG'));

        $this->actingAs($admin)->post(route('admin.giftcard-products.toggle', $product))->assertRedirect();
        $this->assertFalse($product->refresh()->is_active);

        $this->actingAs($admin)->delete(route('admin.giftcard-products.destroy', $product))->assertRedirect();
        $this->assertNull(GiftCardProduct::find($product->id));
    }

    public function test_only_super_admin_can_change_user_roles(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $target = User::factory()->create();
        $target->assignRole('user');

        $this->actingAs($admin)->put(route('admin.users.update-role', $target), ['role' => 'admin'])
            ->assertForbidden();

        $superAdmin = $this->admin();
        $this->actingAs($superAdmin)->put(route('admin.users.update-role', $target), ['role' => 'admin'])
            ->assertRedirect();

        $this->assertTrue($target->refresh()->hasRole('admin'));
    }
}
