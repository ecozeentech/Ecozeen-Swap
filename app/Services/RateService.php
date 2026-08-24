<?php

namespace App\Services;

use App\Models\CryptoAsset;
use App\Models\DailyRate;
use App\Models\FiatCurrency;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Handles the single-vendor daily rate lifecycle: admins set a buy/sell
 * rate for a crypto/fiat pair that automatically expires 24 hours later.
 */
class RateService
{
    public function activeRate(int $cryptoAssetId, int $fiatCurrencyId): ?DailyRate
    {
        return DailyRate::query()
            ->where('crypto_asset_id', $cryptoAssetId)
            ->where('fiat_currency_id', $fiatCurrencyId)
            ->active()
            ->latest('starts_at')
            ->first();
    }

    /**
     * @return Collection<int, DailyRate>
     */
    public function allActiveRates(): Collection
    {
        return DailyRate::query()
            ->with(['cryptoAsset', 'fiatCurrency'])
            ->active()
            ->whereHas('cryptoAsset', fn ($q) => $q->where('is_active', true))
            ->whereHas('fiatCurrency', fn ($q) => $q->where('is_active', true))
            ->orderBy('crypto_asset_id')
            ->get();
    }

    public function setRate(
        int $cryptoAssetId,
        int $fiatCurrencyId,
        float $buyRate,
        float $sellRate,
        ?int $hoursValid,
        User $admin
    ): DailyRate {
        return DB::transaction(function () use ($cryptoAssetId, $fiatCurrencyId, $buyRate, $sellRate, $hoursValid, $admin) {
            DailyRate::query()
                ->where('crypto_asset_id', $cryptoAssetId)
                ->where('fiat_currency_id', $fiatCurrencyId)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $hours = $hoursValid ?: 24;

            return DailyRate::create([
                'crypto_asset_id' => $cryptoAssetId,
                'fiat_currency_id' => $fiatCurrencyId,
                'buy_rate' => $buyRate,
                'sell_rate' => $sellRate,
                'starts_at' => now(),
                'expires_at' => now()->addHours($hours),
                'is_active' => true,
                'created_by' => $admin->id,
            ]);
        });
    }

    public function expireOutdatedRates(): int
    {
        return DailyRate::query()
            ->where('is_active', true)
            ->where('expires_at', '<=', now())
            ->update(['is_active' => false]);
    }

    public function cryptoAssets(): Collection
    {
        return CryptoAsset::query()->active()->orderBy('name')->get();
    }

    public function fiatCurrencies(): Collection
    {
        return FiatCurrency::query()->active()->orderBy('code')->get();
    }
}
