<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $wallets = $request->user()->wallets()->get()->map(function ($wallet) {
            return [
                'currency_type' => $wallet->currency_type,
                'currency_code' => $wallet->currency_code,
                'balance' => (string) $wallet->balance,
                'available_balance' => $wallet->availableBalance(),
            ];
        });

        return response()->json(['data' => $wallets]);
    }
}
