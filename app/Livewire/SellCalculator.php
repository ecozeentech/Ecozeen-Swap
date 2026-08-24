<?php

namespace App\Livewire;

use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Services\RateService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SellCalculator extends Component
{
    public ?int $cryptoAssetId = null;

    public ?int $fiatCurrencyId = null;

    public string $cryptoAmount = '';

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
    public function quote(): ?array
    {
        if (! $this->cryptoAssetId || ! $this->fiatCurrencyId || ! is_numeric($this->cryptoAmount) || (float) $this->cryptoAmount <= 0) {
            return null;
        }

        $crypto = CryptoAsset::find($this->cryptoAssetId);
        $fiat = FiatCurrency::find($this->fiatCurrencyId);

        if (! $crypto || ! $fiat) {
            return null;
        }

        $rate = app(RateService::class)->activeRate($crypto->id, $fiat->id);

        if (! $rate) {
            return ['error' => "No active rate is set for {$crypto->symbol}/{$fiat->code} right now."];
        }

        return [
            'fiat_amount' => bcmul((string) $this->cryptoAmount, (string) $rate->buy_rate, 2),
            'code' => $fiat->code,
            'rate' => $rate,
        ];
    }

    public function render()
    {
        return view('livewire.sell-calculator');
    }
}
