<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BankAccount;
use App\Models\CryptoAsset;
use App\Models\CryptoWallet;
use App\Models\FiatCurrency;
use App\Models\PaymentGateway;
use App\Models\Transaction;
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
    ) {}

    public function index(Request $request): View
    {
        return view('trade.buy-sell', [
            'activeTab' => 'sell',
            'cryptoAssets' => $this->rates->cryptoAssets(),
            'fiatCurrencies' => $this->rates->fiatCurrencies(),
            'activeRates' => $this->rates->allActiveRates(),
            'gateways' => PaymentGateway::query()->active()->get(),
            'bankAccounts' => $request->user()->bankAccounts()->active()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'crypto_asset_id' => ['required', 'exists:crypto_assets,id'],
            'fiat_currency_id' => ['required', 'exists:fiat_currencies,id'],
            'crypto_amount' => ['required', 'numeric', 'min:0.00000001'],
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
        ]);

        $user = $request->user();
        $crypto = CryptoAsset::findOrFail($request->input('crypto_asset_id'));
        $fiat = FiatCurrency::findOrFail($request->input('fiat_currency_id'));
        $bankAccount = BankAccount::query()->where('user_id', $user->id)->findOrFail($request->input('bank_account_id'));

        $platformWallet = CryptoWallet::query()->where('crypto_asset_id', $crypto->id)->active()->orderByDesc('is_default')->first();

        if (! $platformWallet) {
            return back()->withErrors(['crypto_asset_id' => "No receiving address has been configured for {$crypto->symbol} yet. Please contact support."]);
        }

        try {
            $transaction = $this->trades->initiateSell(
                $user,
                $crypto,
                $fiat,
                (float) $request->input('crypto_amount'),
                $platformWallet->wallet_address,
                [
                    'memo_tag' => $platformWallet->memo_tag,
                    'settlement_bank_account_id' => $bankAccount->id,
                    'settlement_bank_name' => $bankAccount->bank_name,
                    'settlement_account_name' => $bankAccount->account_name,
                ]
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
