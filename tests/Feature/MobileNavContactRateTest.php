<?php

namespace Tests\Feature;

use App\Models\CryptoAsset;
use App\Models\DailyRate;
use App\Models\FiatCurrency;
use App\Models\Page;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\RateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileNavContactRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_has_a_mobile_hamburger_menu(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('mobileNavOpen', false);
        $response->assertSee('aria-label="Toggle menu"', false);
    }

    public function test_public_layout_pages_have_a_mobile_hamburger_menu(): void
    {
        Page::create(['slug' => 'about', 'title' => 'About', 'content' => 'x', 'is_published' => true]);

        $response = $this->get(route('about.show'));

        $response->assertOk();
        $response->assertSee('mobileNavOpen', false);
        $response->assertSee('aria-label="Toggle menu"', false);
    }

    public function test_public_mobile_menu_shows_logout_when_authenticated(): void
    {
        Page::create(['slug' => 'about', 'title' => 'About', 'content' => 'x', 'is_published' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('about.show'));

        $response->assertOk();
        $response->assertSee(route('logout'), false);
    }

    public function test_authenticated_mobile_drawer_includes_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        // The mobile drawer and desktop sidebar both include nav-items and
        // a logout form; assert there are now two logout forms (one per
        // sidebar) rather than just one.
        $count = substr_count($response->getContent(), 'action="'.route('logout').'"');
        $this->assertGreaterThanOrEqual(2, $count);
    }

    public function test_admin_mobile_drawer_includes_logout(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $count = substr_count($response->getContent(), 'action="'.route('logout').'"');
        $this->assertGreaterThanOrEqual(2, $count);
    }

    public function test_admin_can_set_contact_phone_and_it_shows_on_contact_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'support_widget_type' => 'none',
            'giftcard_buyback_rate' => 75,
            'default_display_currency' => 'USD',
            'contact_phone' => '+1 (555) 123-4567',
        ])->assertSessionHasNoErrors();

        $this->assertSame('+1 (555) 123-4567', SystemSetting::get('contact_phone'));

        $response = $this->get(route('contact.show'));
        $response->assertOk();
        $response->assertSee('+1 (555) 123-4567');
    }

    public function test_contact_page_hides_phone_card_when_not_set(): void
    {
        $response = $this->get(route('contact.show'));

        $response->assertOk();
        $response->assertDontSee('tel:');
    }

    public function test_admin_can_renew_an_expired_rate_without_deleting_it(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $usd = FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);

        $rate = app(RateService::class)->setRate($btc->id, $usd->id, 64000, 65000, 1, $admin);
        $rate->update(['expires_at' => now()->subHour(), 'is_active' => false]);

        $this->assertNull(app(RateService::class)->activeRate($btc->id, $usd->id));

        $response = $this->actingAs($admin)->post(route('admin.rates.renew', $rate), [
            'hours_valid' => 24,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'rate-renewed');

        $rate->refresh();
        $this->assertTrue($rate->is_active);
        $this->assertFalse($rate->isExpired());
        $this->assertNotNull(app(RateService::class)->activeRate($btc->id, $usd->id));

        // Same row, not a new one.
        $this->assertSame(1, DailyRate::where('crypto_asset_id', $btc->id)->where('fiat_currency_id', $usd->id)->count());
    }

    public function test_renewing_can_deactivate_any_other_active_rate_for_the_same_pair(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $usd = FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);

        $expiredRate = app(RateService::class)->setRate($btc->id, $usd->id, 64000, 65000, 1, $admin);
        $expiredRate->update(['expires_at' => now()->subHour(), 'is_active' => false]);

        $currentRate = app(RateService::class)->setRate($btc->id, $usd->id, 70000, 71000, 24, $admin);
        $this->assertTrue($currentRate->fresh()->is_active);

        $this->actingAs($admin)->post(route('admin.rates.renew', $expiredRate), ['hours_valid' => 24]);

        $this->assertTrue($expiredRate->fresh()->is_active);
        $this->assertFalse($currentRate->fresh()->is_active);
    }

    public function test_toggling_an_expired_rate_on_is_rejected_with_a_helpful_message(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $usd = FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);

        $rate = app(RateService::class)->setRate($btc->id, $usd->id, 64000, 65000, 1, $admin);
        $rate->update(['expires_at' => now()->subHour(), 'is_active' => false]);

        $response = $this->actingAs($admin)->post(route('admin.rates.toggle', $rate));

        $response->assertSessionHasErrors('rate');
        $this->assertFalse($rate->fresh()->is_active);
    }
}
