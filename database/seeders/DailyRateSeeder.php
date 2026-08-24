<?php

namespace Database\Seeders;

use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Models\User;
use App\Services\RateService;
use Illuminate\Database\Seeder;

class DailyRateSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@ecozeenswap.com')->first();

        if (! $admin) {
            return;
        }

        $rateService = app(RateService::class);

        // Illustrative starting rates (USD-denominated approximations).
        $usdRates = [
            'BTC' => ['buy' => 64500, 'sell' => 65200],
            'ETH' => ['buy' => 3150, 'sell' => 3200],
            'USDT' => ['buy' => 0.995, 'sell' => 1.005],
            'BNB' => ['buy' => 570, 'sell' => 580],
        ];

        $fiatMultipliers = [
            'USD' => 1,
            'NGN' => 1610,
            'GHS' => 14.7,
        ];

        foreach ($usdRates as $symbol => $usd) {
            $crypto = CryptoAsset::query()->where('symbol', $symbol)->first();

            if (! $crypto) {
                continue;
            }

            foreach ($fiatMultipliers as $code => $multiplier) {
                $fiat = FiatCurrency::query()->where('code', $code)->first();

                if (! $fiat) {
                    continue;
                }

                $rateService->setRate(
                    $crypto->id,
                    $fiat->id,
                    round($usd['buy'] * $multiplier, 2),
                    round($usd['sell'] * $multiplier, 2),
                    24,
                    $admin
                );
            }
        }
    }
}
