<?php

namespace App\Livewire;

use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Services\RateService;
use Livewire\Component;

class DashboardStats extends Component
{
    public function render()
    {
        $user = auth()->user();
        $wallets = $user->wallets()->orderBy('currency_type')->orderBy('currency_code')->get();
        $rates = app(RateService::class);
        $usd = FiatCurrency::query()->where('code', 'USD')->first();

        $portfolioUsd = 0;

        foreach ($wallets as $wallet) {
            if ($wallet->currency_type === 'fiat') {
                $fiat = FiatCurrency::query()->where('code', $wallet->currency_code)->first();
                $portfolioUsd += (float) $wallet->balance * (float) ($fiat?->exchange_rate_to_usd ?? 0);

                continue;
            }

            $asset = CryptoAsset::query()->where('symbol', $wallet->currency_code)->first();
            if ($asset && $usd) {
                $rate = $rates->activeRate($asset->id, $usd->id);
                if ($rate) {
                    $portfolioUsd += (float) $wallet->balance * (float) $rate->buy_rate;
                }
            }
        }

        return view('livewire.dashboard-stats', [
            'portfolioUsd' => round($portfolioUsd, 2),
            'recentTransactions' => $user->transactions()->latest()->limit(5)->get(),
        ]);
    }
}
