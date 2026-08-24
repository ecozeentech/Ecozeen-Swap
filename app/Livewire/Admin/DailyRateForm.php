<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Services\RateService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DailyRateForm extends Component
{
    public ?int $cryptoAssetId = null;

    public ?int $fiatCurrencyId = null;

    public string $buyRate = '';

    public string $sellRate = '';

    public int $hoursValid = 24;

    public ?string $successMessage = null;

    public function updated($property): void
    {
        if (in_array($property, ['cryptoAssetId', 'fiatCurrencyId'])) {
            $this->successMessage = null;
        }
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

    #[Computed]
    public function currentRate()
    {
        if (! $this->cryptoAssetId || ! $this->fiatCurrencyId) {
            return null;
        }

        return app(RateService::class)->activeRate($this->cryptoAssetId, $this->fiatCurrencyId);
    }

    public function setRate(): void
    {
        $this->validate([
            'cryptoAssetId' => ['required', 'exists:crypto_assets,id'],
            'fiatCurrencyId' => ['required', 'exists:fiat_currencies,id'],
            'buyRate' => ['required', 'numeric', 'min:0'],
            'sellRate' => ['required', 'numeric', 'min:0', 'gte:buyRate'],
            'hoursValid' => ['required', 'integer', 'min:1', 'max:168'],
        ]);

        $rate = app(RateService::class)->setRate(
            $this->cryptoAssetId,
            $this->fiatCurrencyId,
            (float) $this->buyRate,
            (float) $this->sellRate,
            $this->hoursValid,
            auth()->user()
        );

        ActivityLog::record(auth()->id(), 'admin_set_daily_rate', [
            'crypto_asset_id' => $rate->crypto_asset_id,
            'fiat_currency_id' => $rate->fiat_currency_id,
        ]);

        $this->successMessage = 'Rate set successfully. Expires '.$rate->expires_at->diffForHumans().'.';
        $this->buyRate = '';
        $this->sellRate = '';

        $this->dispatch('rate-updated');
    }

    public function render()
    {
        return view('livewire.admin.daily-rate-form');
    }
}
