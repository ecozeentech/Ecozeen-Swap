<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CryptoAsset;
use App\Services\RateService;
use App\Services\TradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class SwapController extends Controller
{
    public function __construct(protected TradeService $trades, protected RateService $rates) {}

    public function index(Request $request): View
    {
        return view('trade.swap', [
            'cryptoAssets' => $this->rates->cryptoAssets(),
            'wallets' => $request->user()->wallets()->where('currency_type', 'crypto')->get(),
        ]);
    }

    public function quote(Request $request): JsonResponse
    {
        $request->validate([
            'from_asset_id' => ['required', 'exists:crypto_assets,id', 'different:to_asset_id'],
            'to_asset_id' => ['required', 'exists:crypto_assets,id'],
            'amount' => ['required', 'numeric', 'min:0.00000001'],
        ]);

        $from = CryptoAsset::findOrFail($request->input('from_asset_id'));
        $to = CryptoAsset::findOrFail($request->input('to_asset_id'));

        try {
            $quote = $this->trades->quoteSwap($request->user(), $from, $to, (float) $request->input('amount'));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'token' => $quote['token'],
            'from_amount' => (string) $quote['from_amount'],
            'to_amount' => (string) $quote['to_amount'],
            'from_symbol' => $from->symbol,
            'to_symbol' => $to->symbol,
            'expires_at' => $quote['expires_at']->toIso8601String(),
            'expires_in_seconds' => 300,
        ]);
    }

    public function execute(Request $request): RedirectResponse
    {
        $request->validate(['token' => ['required', 'string']]);

        try {
            $transaction = $this->trades->executeSwap($request->user(), $request->input('token'));
        } catch (RuntimeException $e) {
            return back()->withErrors(['token' => $e->getMessage()]);
        }

        ActivityLog::record($request->user()->id, 'swap_executed', ['reference' => $transaction->reference]);

        return redirect()->route('swap.index')->with('status', 'swap-successful');
    }
}
