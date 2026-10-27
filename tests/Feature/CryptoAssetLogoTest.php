<?php

namespace Tests\Feature;

use App\Models\CryptoAsset;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CryptoAssetLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_usdt_falls_back_to_its_bundled_brand_logo_when_no_custom_logo_is_uploaded(): void
    {
        $usdt = CryptoAsset::create(['name' => 'Tether USD', 'symbol' => 'USDT', 'network' => 'TRC20', 'decimal_places' => 6, 'is_active' => true]);

        $this->assertStringEndsWith('images/crypto/usdt.svg', $usdt->logoUrl());
        $this->assertStringNotContainsString('crypto-placeholder', $usdt->logoUrl());
    }

    public function test_every_seeded_default_coin_has_a_bundled_logo(): void
    {
        foreach (['btc', 'eth', 'usdt', 'bnb'] as $symbol) {
            $this->assertNotNull(CryptoAsset::bundledLogoUrl($symbol), "Expected a bundled logo for {$symbol}");
        }
    }

    public function test_an_unknown_symbol_falls_back_to_the_generic_placeholder(): void
    {
        $asset = CryptoAsset::create(['name' => 'Some New Coin', 'symbol' => 'ZZZCOIN', 'decimal_places' => 8, 'is_active' => true]);

        $this->assertStringContainsString('crypto-placeholder', $asset->logoUrl());
    }

    public function test_an_admin_uploaded_logo_takes_priority_over_the_bundled_fallback(): void
    {
        $usdt = CryptoAsset::create(['name' => 'Tether USD', 'symbol' => 'USDT', 'network' => 'TRC20', 'decimal_places' => 6, 'is_active' => true, 'logo' => 'crypto-logos/custom-usdt.png']);

        $this->assertStringContainsString('custom-usdt.png', $usdt->logoUrl());
    }

    public function test_usdt_shows_a_logo_on_the_user_dashboard_with_no_custom_logo_uploaded(): void
    {
        $usdt = CryptoAsset::create(['name' => 'Tether USD', 'symbol' => 'USDT', 'network' => 'TRC20', 'decimal_places' => 6, 'is_active' => true]);
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'currency_type' => 'crypto', 'currency_code' => 'USDT', 'balance' => 100, 'reserved_balance' => 0]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('images/crypto/usdt.svg', false);
    }
}
