<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RateService;
use Illuminate\Http\JsonResponse;

class RateApiController extends Controller
{
    public function __construct(protected RateService $rates) {}

    public function index(): JsonResponse
    {
        $rates = $this->rates->allActiveRates()->map(function ($rate) {
            return [
                'id' => $rate->id,
                'crypto' => $rate->cryptoAsset->symbol,
                'fiat' => $rate->fiatCurrency->code,
                'buy_rate' => (string) $rate->buy_rate,
                'sell_rate' => (string) $rate->sell_rate,
                'expires_at' => $rate->expires_at->toIso8601String(),
                'seconds_remaining' => $rate->secondsRemaining(),
            ];
        });

        return response()->json(['data' => $rates]);
    }
}
