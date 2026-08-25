<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BankAccount;
use App\Models\CryptoAsset;
use App\Models\CryptoWallet;
use App\Models\DepositProof;
use App\Models\FiatCurrency;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Notifications\AccountNotification;
use App\Services\FlutterwaveService;
use App\Services\PaystackService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $wallets,
        protected PaystackService $paystack,
        protected FlutterwaveService $flutterwave,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $wallets = $user->wallets()->orderBy('currency_type')->orderBy('currency_code')->get();
        $transactions = $user->transactions()->latest()->limit(15)->get();
        $gateways = PaymentGateway::query()->active()->get();

        return view('wallet.index', [
            'wallets' => $wallets,
            'transactions' => $transactions,
            'gateways' => $gateways,
            'fiatCurrencies' => FiatCurrency::query()->active()->get(),
            'cryptoAssets' => CryptoAsset::query()->active()->get(),
        ]);
    }

    public function fundForm(Request $request): View
    {
        return view('wallet.fund', [
            'fiatCurrencies' => FiatCurrency::query()->active()->get(),
            'gateways' => PaymentGateway::query()->active()->get(),
        ]);
    }

    public function fund(Request $request): RedirectResponse
    {
        $request->validate([
            'fiat_currency' => ['required', 'exists:fiat_currencies,code'],
            'amount' => ['required', 'numeric', 'min:1'],
            'gateway' => ['required', 'in:paystack,flutterwave,bank_transfer'],
        ]);

        $user = $request->user();
        $fiat = FiatCurrency::query()->where('code', $request->input('fiat_currency'))->firstOrFail();
        $wallet = $this->wallets->getOrCreateWallet($user, 'fiat', $fiat->code);
        $reference = Transaction::generateReference('DEP');

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'deposit',
            'amount' => $request->input('amount'),
            'currency_code' => $fiat->code,
            'status' => 'pending',
            'reference' => $reference,
            'metadata' => ['gateway' => $request->input('gateway')],
        ]);

        if ($request->input('gateway') === 'bank_transfer') {
            return redirect()->route('wallet.fund.bank', $transaction)->with('status', 'awaiting-bank-transfer');
        }

        try {
            if ($request->input('gateway') === 'paystack') {
                $response = $this->paystack->initializeTransaction(
                    $user->email,
                    $request->input('amount') * 100,
                    $reference,
                    ['transaction_id' => $transaction->id]
                );

                $url = $response['data']['authorization_url'] ?? null;
            } else {
                $response = $this->flutterwave->initializeTransaction(
                    $user->email,
                    $request->input('amount'),
                    $fiat->code,
                    $reference,
                    ['transaction_id' => $transaction->id]
                );

                $url = $response['data']['link'] ?? null;
            }
        } catch (Throwable $e) {
            Log::error('Payment gateway initialization failed', ['error' => $e->getMessage()]);
            $transaction->update(['status' => 'failed', 'admin_note' => $e->getMessage()]);

            return redirect()->route('wallet.fund')->withErrors(['gateway' => 'Unable to reach the payment gateway. Please try again or use bank transfer.']);
        }

        if (! $url) {
            $transaction->update(['status' => 'failed']);

            return redirect()->route('wallet.fund')->withErrors(['gateway' => 'The payment gateway did not return a checkout link.']);
        }

        return redirect()->away($url);
    }

    public function fundBankForm(Transaction $transaction): View
    {
        abort_unless($transaction->user_id === auth()->id(), 403);

        $bankGateway = PaymentGateway::query()->where('slug', 'bank_transfer')->first();

        return view('wallet.fund-bank', [
            'transaction' => $transaction,
            'bankDetails' => $bankGateway?->metadata ?? [],
        ]);
    }

    public function uploadProof(Request $request, Transaction $transaction): RedirectResponse
    {
        abort_unless($transaction->user_id === $request->user()->id, 403);

        $request->validate([
            'proof' => ['required', 'file', 'image', 'max:5120'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $path = $request->file('proof')->store('deposit-proofs/'.$request->user()->id, 'local');

        DepositProof::create([
            'transaction_id' => $transaction->id,
            'user_id' => $request->user()->id,
            'file_path' => $path,
            'note' => $request->input('note'),
        ]);

        $transaction->update(['status' => 'processing']);

        ActivityLog::record($request->user()->id, 'deposit_proof_uploaded', ['transaction' => $transaction->reference]);

        return redirect()->route('wallet.index')->with('status', 'proof-uploaded');
    }

    public function fundCallback(Request $request, string $gateway): RedirectResponse
    {
        $reference = $request->input('reference') ?? $request->input('tx_ref');
        $transaction = Transaction::query()->where('reference', $reference)->first();

        if (! $transaction) {
            return redirect()->route('wallet.index')->withErrors(['gateway' => 'Transaction not found.']);
        }

        try {
            $verified = $gateway === 'paystack'
                ? (($this->paystack->verifyTransaction($reference)['data']['status'] ?? null) === 'success')
                : (($this->flutterwave->verifyTransaction($request->input('transaction_id'))['data']['status'] ?? null) === 'successful');
        } catch (Throwable $e) {
            Log::error('Payment verification failed', ['error' => $e->getMessage()]);
            $verified = false;
        }

        if ($verified) {
            $this->creditDeposit($transaction);

            return redirect()->route('wallet.index')->with('status', 'deposit-successful');
        }

        return redirect()->route('wallet.index')->withErrors(['gateway' => 'We could not confirm your payment yet. It will be credited automatically once confirmed.']);
    }

    public function creditDeposit(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $transaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($transaction->status === 'completed') {
                return;
            }

            $wallet = $this->wallets->getOrCreateWallet($transaction->user, 'fiat', $transaction->currency_code);
            $wallet->increment('balance', $transaction->amount);

            $transaction->update(['status' => 'completed', 'processed_at' => now()]);
        });

        $transaction->user->notify(new AccountNotification(
            'Deposit Confirmed',
            number_format((float) $transaction->amount, 2)." {$transaction->currency_code} has been credited to your wallet.",
            'success',
            route('wallet.index')
        ));
    }

    public function depositAddress(Request $request, CryptoAsset $cryptoAsset): View
    {
        $wallets = CryptoWallet::query()
            ->where('crypto_asset_id', $cryptoAsset->id)
            ->active()
            ->orderByDesc('is_default')
            ->get();

        return view('wallet.deposit-crypto', ['asset' => $cryptoAsset, 'platformWallets' => $wallets]);
    }

    public function withdrawForm(Request $request): View
    {
        return view('wallet.withdraw', [
            'fiatCurrencies' => FiatCurrency::query()->active()->get(),
            'cryptoAssets' => CryptoAsset::query()->active()->get(),
            'wallets' => $request->user()->wallets()->get(),
            'bankAccounts' => $request->user()->bankAccounts()->active()->get(),
        ]);
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $request->validate([
            'currency_type' => ['required', 'in:fiat,crypto'],
            'currency_code' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.00000001'],
            'bank_account_id' => ['required_if:currency_type,fiat', 'nullable', 'exists:bank_accounts,id'],
            'destination' => ['required_if:currency_type,crypto', 'nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $wallet = $this->wallets->getOrCreateWallet($user, $request->input('currency_type'), $request->input('currency_code'));

        if (! $this->wallets->hasSufficientBalance($wallet, $request->input('amount'))) {
            return back()->withErrors(['amount' => 'Insufficient available balance for this withdrawal.']);
        }

        $destinationMeta = ['destination' => $request->input('destination')];

        if ($request->input('currency_type') === 'fiat') {
            $bankAccount = BankAccount::query()->where('user_id', $user->id)->findOrFail($request->input('bank_account_id'));
            $destinationMeta = [
                'bank_account_id' => $bankAccount->id,
                'bank_name' => $bankAccount->bank_name,
                'account_name' => $bankAccount->account_name,
                'account_number_last4' => substr((string) $bankAccount->account_number, -4),
            ];
        }

        DB::transaction(function () use ($wallet, $request, $user, $destinationMeta) {
            $locked = Wallet::query()->lockForUpdate()->find($wallet->id);
            $locked->reserved_balance = bcadd((string) $locked->reserved_balance, (string) $request->input('amount'), 8);
            $locked->save();

            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $locked->id,
                'type' => 'withdrawal',
                'amount' => $request->input('amount'),
                'currency_code' => $locked->currency_code,
                'status' => 'pending',
                'reference' => Transaction::generateReference('WD'),
                'metadata' => $destinationMeta,
            ]);
        });

        ActivityLog::record($user->id, 'withdrawal_requested', ['amount' => $request->input('amount'), 'currency' => $request->input('currency_code')]);

        return redirect()->route('wallet.index')->with('status', 'withdrawal-requested');
    }
}
