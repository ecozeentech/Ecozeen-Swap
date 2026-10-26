<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\CryptoAsset;
use App\Models\DailyRate;
use App\Models\FiatCurrency;
use App\Services\RateService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Renders the "Active Rates" + "Rate History" sections on the admin daily
 * rates page. Polls every 10 seconds (wire:poll) so a rate set from
 * another tab/admin, or one expiring in the background, shows up without
 * a manual page reload — and listens for the sibling DailyRateForm's
 * "rate-updated" event for an instant refresh right after saving.
 *
 * The open/closed state for each row's edit/renew panel lives here as
 * component state (keyed by rate id) instead of Alpine x-data on a <tr>,
 * which can't be shared with its sibling <tr> — that mismatch is why the
 * old plain-Blade "Renew" button never visibly opened anything.
 */
class DailyRatesPanel extends Component
{
    public ?int $renewingRateId = null;

    public string $renewBuyRate = '';

    public string $renewSellRate = '';

    public string $renewHoursValid = '24';

    public ?string $errorMessage = null;

    #[On('rate-updated')]
    public function refreshNow(): void
    {
        // No-op render trigger; Livewire re-fetches computed properties
        // (activeRates/history) on every render automatically.
    }

    public function toggleRenew(int $rateId): void
    {
        $this->errorMessage = null;

        if ($this->renewingRateId === $rateId) {
            $this->renewingRateId = null;

            return;
        }

        $rate = DailyRate::find($rateId);
        $this->renewingRateId = $rateId;
        $this->renewBuyRate = $rate ? (string) $rate->buy_rate : '';
        $this->renewSellRate = $rate ? (string) $rate->sell_rate : '';
        $this->renewHoursValid = '24';
    }

    public function renew(int $rateId): void
    {
        $this->errorMessage = null;

        $this->validate([
            'renewBuyRate' => ['required', 'numeric', 'min:0'],
            'renewSellRate' => ['required', 'numeric', 'min:0', 'gte:renewBuyRate'],
            'renewHoursValid' => ['required', 'integer', 'min:1'],
        ]);

        $rate = DailyRate::findOrFail($rateId);

        app(RateService::class)->renewRate(
            $rate,
            (int) $this->renewHoursValid,
            (float) $this->renewBuyRate,
            (float) $this->renewSellRate,
        );

        ActivityLog::record(auth()->id(), 'admin_renewed_daily_rate', [
            'rate_id' => $rate->id,
            'crypto_asset_id' => $rate->crypto_asset_id,
            'fiat_currency_id' => $rate->fiat_currency_id,
        ]);

        $this->renewingRateId = null;
        $this->dispatch('rate-updated');
    }

    public function toggleActive(int $rateId): void
    {
        $this->errorMessage = null;
        $rate = DailyRate::findOrFail($rateId);

        if ($rate->isExpired() && ! $rate->is_active) {
            $this->errorMessage = 'This rate has expired — use "Renew" to reactivate it with a fresh expiry window instead.';

            return;
        }

        $rate->update(['is_active' => ! $rate->is_active]);

        ActivityLog::record(auth()->id(), $rate->is_active ? 'admin_activated_daily_rate' : 'admin_deactivated_daily_rate', ['rate_id' => $rate->id]);
    }

    public function destroy(int $rateId): void
    {
        $rate = DailyRate::findOrFail($rateId);

        ActivityLog::record(auth()->id(), 'admin_deleted_daily_rate', [
            'rate_id' => $rate->id,
            'crypto_asset_id' => $rate->crypto_asset_id,
            'fiat_currency_id' => $rate->fiat_currency_id,
        ]);

        $rate->delete();
        $this->renewingRateId = null;
    }

    #[Computed]
    public function cryptoAssets()
    {
        return CryptoAsset::query()->active()->orderBy('name')->get();
    }

    #[Computed]
    public function fiatCurrencies()
    {
        return FiatCurrency::query()->active()->orderBy('code')->get();
    }

    public function render()
    {
        return view('livewire.admin.daily-rates-panel', [
            'activeRates' => app(RateService::class)->allActiveRates(),
            'history' => DailyRate::query()->with(['cryptoAsset', 'fiatCurrency', 'createdBy'])->latest()->limit(30)->get(),
        ]);
    }
}
