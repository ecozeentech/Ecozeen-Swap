<?php

namespace Tests\Feature;

use App\Livewire\Admin\DailyRateForm;
use App\Livewire\DashboardStats;
use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\AdminAlert;
use App\Services\RateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class RatesTogglesCurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_a_rate_for_a_brand_new_crypto_fiat_pair(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $ghs = FiatCurrency::create(['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => '₵', 'exchange_rate_to_usd' => 0.068, 'is_active' => true]);

        $this->assertNull(app(RateService::class)->activeRate($btc->id, $ghs->id));

        Livewire::actingAs($admin)
            ->test(DailyRateForm::class)
            ->set('cryptoAssetId', $btc->id)
            ->set('fiatCurrencyId', $ghs->id)
            ->set('buyRate', '95000')
            ->set('sellRate', '96000')
            ->set('hoursValid', 24)
            ->call('setRate')
            ->assertHasNoErrors();

        $this->assertNotNull(app(RateService::class)->activeRate($btc->id, $ghs->id));
    }

    public function test_admin_can_change_the_pair_on_an_existing_rate(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $eth = CryptoAsset::create(['name' => 'Ethereum', 'symbol' => 'ETH', 'decimal_places' => 8, 'is_active' => true]);
        $usd = FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);

        $rate = app(RateService::class)->setRate($btc->id, $usd->id, 64000, 65000, 24, $admin);

        $response = $this->actingAs($admin)->put(route('admin.rates.update', $rate), [
            'crypto_asset_id' => $eth->id,
            'fiat_currency_id' => $usd->id,
            'buy_rate' => 3400,
            'sell_rate' => 3450,
            'is_active' => 1,
        ]);

        $response->assertRedirect();
        $rate->refresh();
        $this->assertSame($eth->id, $rate->crypto_asset_id);
        $this->assertNull(app(RateService::class)->activeRate($btc->id, $usd->id));
        $this->assertNotNull(app(RateService::class)->activeRate($eth->id, $usd->id));
    }

    public function test_admin_can_add_the_same_symbol_on_a_different_network(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)->post(route('admin.crypto.store'), [
            'name' => 'Tether', 'symbol' => 'USDT', 'network' => 'ERC20', 'decimal_places' => 6,
        ])->assertSessionHasNoErrors();

        // Same symbol, different network: must succeed.
        $response = $this->actingAs($admin)->post(route('admin.crypto.store'), [
            'name' => 'Tether', 'symbol' => 'usdt', 'network' => 'TRC20', 'decimal_places' => 6,
        ]);
        $response->assertSessionHasNoErrors();

        $this->assertSame(2, CryptoAsset::where('symbol', 'USDT')->count());

        // Same symbol AND same network again: must be rejected.
        $duplicate = $this->actingAs($admin)->post(route('admin.crypto.store'), [
            'name' => 'Tether', 'symbol' => 'USDT', 'network' => 'ERC20', 'decimal_places' => 6,
        ]);
        $duplicate->assertSessionHasErrors('symbol');
        $this->assertSame(2, CryptoAsset::where('symbol', 'USDT')->count());
    }

    public function test_disabling_sell_hides_the_sell_form_even_when_reached_via_the_buy_route(): void
    {
        $user = User::factory()->create();
        SystemSetting::set('sell_enabled', false, 'boolean', 'features');

        $response = $this->actingAs($user)->get(route('buy.index'));

        $response->assertOk();
        $response->assertDontSee('sell.store', false);
        $response->assertSee('temporarily unavailable');
    }

    public function test_disabled_feature_route_shows_coming_soon(): void
    {
        $user = User::factory()->create();
        SystemSetting::set('withdrawals_enabled', false, 'boolean', 'features');

        $this->actingAs($user)->get(route('wallet.withdraw'))->assertSee('Coming Soon');
    }

    public function test_toggling_deposits_off_blocks_the_deposit_form_and_crypto_deposit_page(): void
    {
        $user = User::factory()->create();
        $btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        SystemSetting::set('deposits_enabled', false, 'boolean', 'features');

        $this->actingAs($user)->get(route('wallet.fund'))->assertSee('Coming Soon');
        $this->actingAs($user)->get(route('wallet.deposit', $btc))->assertSee('Coming Soon');
    }

    public function test_new_registration_notifies_admins(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->post(route('register'), [
            'name' => 'New Person',
            'username' => 'newperson',
            'email' => 'newperson@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $this->assertGreaterThanOrEqual(1, $admin->notifications()->count());
        $this->assertSame('New User Registered', $admin->notifications()->first()->data['title']);
    }

    public function test_kyc_submission_notifies_admins(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('id.jpg');

        $this->actingAs($user)->post(route('security.kyc.store'), [
            'document_type' => 'id_card',
            'file' => $file,
        ]);

        Notification::assertSentTo($admin, AdminAlert::class);
    }

    public function test_admin_can_set_default_display_currency_and_user_dashboard_reflects_it(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        FiatCurrency::create(['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦', 'exchange_rate_to_usd' => 0.00062, 'is_active' => true]);
        FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'support_widget_type' => 'none',
            'giftcard_buyback_rate' => 75,
            'default_display_currency' => 'NGN',
        ])->assertSessionHasNoErrors();

        $this->assertSame('NGN', SystemSetting::get('default_display_currency'));

        $user = User::factory()->create();
        $this->assertSame('NGN', $user->displayCurrencyCode());
    }

    public function test_user_can_switch_their_own_display_currency_on_the_dashboard(): void
    {
        FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);
        FiatCurrency::create(['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => '₵', 'exchange_rate_to_usd' => 0.068, 'is_active' => true]);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DashboardStats::class)
            ->set('selectedCurrency', 'GHS');

        $this->assertSame('GHS', $user->refresh()->display_currency);
    }
}
