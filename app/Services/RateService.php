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

    /**
     * Bring an existing (usually expired) rate back to life for another
     * window instead of forcing the admin to delete it and create a new
     * one from scratch. Keeps the same buy/sell rate values by default —
     * pass new ones to adjust them at the same time.
     */
    public function renewRate(DailyRate $dailyRate, ?int $hoursValid = null, ?float $buyRate = null, ?float $sellRate = null): DailyRate
    {
        return DB::transaction(function () use ($dailyRate, $hoursValid, $buyRate, $sellRate) {
            DailyRate::query()
                ->where('crypto_asset_id', $dailyRate->crypto_asset_id)
                ->where('fiat_currency_id', $dailyRate->fiat_currency_id)
                ->where('id', '!=', $dailyRate->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $dailyRate->update([
                'buy_rate' => $buyRate ?? $dailyRate->buy_rate,
                'sell_rate' => $sellRate ?? $dailyRate->sell_rate,
                'starts_at' => now(),
                'expires_at' => now()->addHours($hoursValid ?: 24),
                'is_active' => true,
            ]);

            return $dailyRate->fresh();
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
