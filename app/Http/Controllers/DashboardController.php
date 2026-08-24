<?php

namespace App\Http\Controllers;

use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Services\RateService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected RateService $rates) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $wallets = $user->wallets()->orderBy('currency_type')->orderBy('currency_code')->get();

        $usdRate = FiatCurrency::query()->where('code', 'USD')->first();
        $portfolioUsd = $this->estimatePortfolioUsd($wallets);

        $recentTransactions = $user->transactions()->latest()->limit(5)->get();

        $activeRates = $this->rates->allActiveRates();

        return view('dashboard', [
            'wallets' => $wallets,
            'portfolioUsd' => $portfolioUsd,
            'recentTransactions' => $recentTransactions,
            'activeRates' => $activeRates,
            'cryptoAssets' => CryptoAsset::query()->get(),
            'fiatCurrencies' => FiatCurrency::query()->get(),
        ]);
    }

    protected function estimatePortfolioUsd($wallets): float
    {
        $total = 0;

        foreach ($wallets as $wallet) {
            if ($wallet->currency_type === 'fiat') {
                $fiat = FiatCurrency::query()->where('code', $wallet->currency_code)->first();
                $total += (float) $wallet->balance * (float) ($fiat?->exchange_rate_to_usd ?? 0);

                continue;
            }

            $usd = FiatCurrency::query()->where('code', 'USD')->first();
            $asset = CryptoAsset::query()->where('symbol', $wallet->currency_code)->first();

            if ($asset && $usd) {
                $rate = $this->rates->activeRate($asset->id, $usd->id);
                if ($rate) {
                    $total += (float) $wallet->balance * (float) $rate->buy_rate;
                }
            }
        }

        return round($total, 2);
    }
}
