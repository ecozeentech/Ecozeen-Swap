<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Models\Transaction;
use App\Services\CryptoAddressService;
use App\Services\RateService;
use App\Services\TradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class SellController extends Controller
{
    public function __construct(
        protected TradeService $trades,
        protected RateService $rates,
        protected CryptoAddressService $addresses,
    ) {}

    public function index(): View
    {
        return view('trade.sell', [
            'cryptoAssets' => $this->rates->cryptoAssets(),
            'fiatCurrencies' => $this->rates->fiatCurrencies(),
            'activeRates' => $this->rates->allActiveRates(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'crypto_asset_id' => ['required', 'exists:crypto_assets,id'],
            'fiat_currency_id' => ['required', 'exists:fiat_currencies,id'],
            'crypto_amount' => ['required', 'numeric', 'min:0.00000001'],
        ]);

        $user = $request->user();
        $crypto = CryptoAsset::findOrFail($request->input('crypto_asset_id'));
        $fiat = FiatCurrency::findOrFail($request->input('fiat_currency_id'));
        $address = $this->addresses->addressFor($user, $crypto);

        try {
            $transaction = $this->trades->initiateSell(
                $user,
                $crypto,
                $fiat,
                (float) $request->input('crypto_amount'),
                $address->address
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['crypto_amount' => $e->getMessage()]);
        }

        ActivityLog::record($user->id, 'sell_initiated', ['reference' => $transaction->reference]);

        return redirect()->route('sell.show', $transaction)->with('status', 'sell-initiated');
    }

    public function show(Request $request, Transaction $transaction): View
    {
        abort_unless($transaction->user_id === $request->user()->id, 403);

        return view('trade.sell-instructions', ['transaction' => $transaction]);
    }
}
