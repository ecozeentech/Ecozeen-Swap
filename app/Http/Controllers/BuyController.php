<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Models\PaymentGateway;
use App\Services\FlutterwaveService;
use App\Services\PaystackService;
use App\Services\RateService;
use App\Services\TradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class BuyController extends Controller
{
    public function __construct(
        protected TradeService $trades,
        protected RateService $rates,
        protected PaystackService $paystack,
    ) {}

    public function index(): View
    {
        return view('trade.buy', [
            'cryptoAssets' => $this->rates->cryptoAssets(),
            'fiatCurrencies' => $this->rates->fiatCurrencies(),
            'activeRates' => $this->rates->allActiveRates(),
            'gateways' => PaymentGateway::query()->active()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'crypto_asset_id' => ['required', 'exists:crypto_assets,id'],
            'fiat_currency_id' => ['required', 'exists:fiat_currencies,id'],
            'fiat_amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:paystack,flutterwave,bank_transfer'],
        ]);

        $user = $request->user();
        $crypto = CryptoAsset::findOrFail($request->input('crypto_asset_id'));
        $fiat = FiatCurrency::findOrFail($request->input('fiat_currency_id'));

        try {
            $transaction = $this->trades->initiateBuy(
                $user,
                $crypto,
                $fiat,
                (float) $request->input('fiat_amount'),
                $request->input('payment_method')
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['fiat_amount' => $e->getMessage()]);
        }

        ActivityLog::record($user->id, 'buy_initiated', ['reference' => $transaction->reference]);

        if ($request->input('payment_method') === 'bank_transfer') {
            return redirect()->route('wallet.fund.bank', $transaction);
        }

        try {
            if ($request->input('payment_method') === 'paystack') {
                $response = $this->paystack->initializeTransaction(
                    $user->email,
                    (float) $request->input('fiat_amount') * 100,
                    $transaction->reference,
                    ['transaction_id' => $transaction->id, 'purpose' => 'buy']
                );
                $url = $response['data']['authorization_url'] ?? null;
            } else {
                $response = app(FlutterwaveService::class)->initializeTransaction(
                    $user->email,
                    (float) $request->input('fiat_amount'),
                    $fiat->code,
                    $transaction->reference,
                    ['transaction_id' => $transaction->id, 'purpose' => 'buy']
                );
                $url = $response['data']['link'] ?? null;
            }
        } catch (Throwable $e) {
            $transaction->update(['status' => 'failed', 'admin_note' => $e->getMessage()]);

            return redirect()->route('buy.index')->withErrors(['payment_method' => 'Unable to reach the payment gateway. Please try bank transfer instead.']);
        }

        if (! $url) {
            $transaction->update(['status' => 'failed']);

            return redirect()->route('buy.index')->withErrors(['payment_method' => 'The payment gateway did not return a checkout link.']);
        }

        return redirect()->away($url);
    }
}
