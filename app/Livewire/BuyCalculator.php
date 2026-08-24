<?php

namespace App\Livewire;

use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Services\RateService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BuyCalculator extends Component
{
    public ?int $cryptoAssetId = null;

    public ?int $fiatCurrencyId = null;

    public string $fiatAmount = '';

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
        if (! $this->cryptoAssetId || ! $this->fiatCurrencyId || ! is_numeric($this->fiatAmount) || (float) $this->fiatAmount <= 0) {
            return null;
        }

        $crypto = CryptoAsset::find($this->cryptoAssetId);
        $fiat = FiatCurrency::find($this->fiatCurrencyId);

        if (! $crypto || ! $fiat) {
            return null;
        }

        try {
            $result = app(RateService::class)->activeRate($crypto->id, $fiat->id);

            if (! $result) {
                return ['error' => "No active rate is set for {$crypto->symbol}/{$fiat->code} right now."];
            }

            $cryptoAmount = bcdiv((string) $this->fiatAmount, (string) $result->sell_rate, $crypto->decimal_places);

            return [
                'crypto_amount' => $cryptoAmount,
                'symbol' => $crypto->symbol,
                'rate' => $result,
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function render()
    {
        return view('livewire.buy-calculator');
    }
}
