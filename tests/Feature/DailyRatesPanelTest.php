<?php

namespace Tests\Feature;

use App\Livewire\Admin\DailyRatesPanel;
use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Models\User;
use App\Services\RateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DailyRatesPanelTest extends TestCase
{
    use RefreshDatabase;

    protected CryptoAsset $btc;

    protected FiatCurrency $usd;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $this->usd = FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('super-admin');
    }

    public function test_renew_button_opens_the_renew_panel_for_the_correct_row(): void
    {
        $this->actingAs($this->admin);
        $rate = app(RateService::class)->setRate($this->btc->id, $this->usd->id, 64000, 65000, 1, $this->admin);
        $rate->update(['expires_at' => now()->subHour(), 'is_active' => false]);

        Livewire::test(DailyRatesPanel::class)
            ->assertSet('renewingRateId', null)
            ->call('toggleRenew', $rate->id)
            ->assertSet('renewingRateId', $rate->id)
            ->assertSet('renewBuyRate', '64000.00000000')
            ->call('toggleRenew', $rate->id)
            ->assertSet('renewingRateId', null);
    }

    public function test_renew_accepts_more_than_168_hours(): void
    {
        $this->actingAs($this->admin);
        $rate = app(RateService::class)->setRate($this->btc->id, $this->usd->id, 64000, 65000, 1, $this->admin);
        $rate->update(['expires_at' => now()->subHour(), 'is_active' => false]);

        Livewire::test(DailyRatesPanel::class)
            ->call('toggleRenew', $rate->id)
            ->set('renewBuyRate', '64000')
            ->set('renewSellRate', '65000')
            ->set('renewHoursValid', '8760') // 1 year — well beyond the old 168h cap
            ->call('renew', $rate->id)
            ->assertHasNoErrors();

        $rate->refresh();
        $this->assertTrue($rate->is_active);
        $this->assertTrue($rate->expires_at->greaterThan(now()->addDays(300)));
    }

    public function test_renew_validates_sell_rate_not_below_buy_rate(): void
    {
        $this->actingAs($this->admin);
        $rate = app(RateService::class)->setRate($this->btc->id, $this->usd->id, 64000, 65000, 1, $this->admin);
        $rate->update(['expires_at' => now()->subHour(), 'is_active' => false]);

        Livewire::test(DailyRatesPanel::class)
            ->call('toggleRenew', $rate->id)
            ->set('renewBuyRate', '70000')
            ->set('renewSellRate', '65000')
            ->set('renewHoursValid', '24')
            ->call('renew', $rate->id)
            ->assertHasErrors(['renewSellRate']);
    }

    public function test_destroy_removes_the_rate(): void
    {
        $this->actingAs($this->admin);
        $rate = app(RateService::class)->setRate($this->btc->id, $this->usd->id, 64000, 65000, 24, $this->admin);

        Livewire::test(DailyRatesPanel::class)->call('destroy', $rate->id);

        $this->assertDatabaseMissing('daily_rates', ['id' => $rate->id]);
    }

    public function test_toggle_active_rejects_an_expired_rate_with_a_helpful_message(): void
    {
        $this->actingAs($this->admin);
        $rate = app(RateService::class)->setRate($this->btc->id, $this->usd->id, 64000, 65000, 1, $this->admin);
        $rate->update(['expires_at' => now()->subHour(), 'is_active' => false]);

        Livewire::test(DailyRatesPanel::class)
            ->call('toggleActive', $rate->id)
            ->assertSet('errorMessage', fn ($message) => str_contains($message, 'Renew'));

        $this->assertFalse($rate->fresh()->is_active);
    }

    public function test_store_rate_accepts_more_than_168_hours(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.rates.store'), [
            'crypto_asset_id' => $this->btc->id,
            'fiat_currency_id' => $this->usd->id,
            'buy_rate' => 64000,
            'sell_rate' => 65000,
            'hours_valid' => 720, // 30 days
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
    }
}
