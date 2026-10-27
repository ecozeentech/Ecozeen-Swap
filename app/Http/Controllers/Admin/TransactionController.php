<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Notifications\TransactionStatusUpdated;
use App\Services\PaymentSettlement;
use App\Services\TradeService;
use App\Services\WalletService;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class TransactionController extends Controller
{
    public function __construct(
        protected TradeService $trades,
        protected WalletService $wallets,
        protected PaymentSettlement $settlements,
    ) {}

    public function index(Request $request): View
    {
        $transactions = Transaction::query()
            ->with('user')
            ->when($request->input('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('reference', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.transactions.index', ['transactions' => $transactions]);
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load(['user', 'wallet']);

        return view('admin.transactions.show', ['transaction' => $transaction]);
    }

    public function markPaid(Transaction $transaction): RedirectResponse
    {
        if (! in_array($transaction->type, ['buy', 'deposit'], true)) {
            return back()->withErrors(['transaction' => "A {$transaction->type} order can't be completed with Mark as Paid — use the matching action for this order type instead."]);
        }

        $alreadyCompleted = $transaction->status === 'completed';
        $settled = $this->settlements->settle($transaction, auth()->user());

        if (! $alreadyCompleted && $settled->status === 'completed') {
            Notify::send($settled->user, new TransactionStatusUpdated($settled));
        }

        ActivityLog::record(auth()->id(), 'admin_marked_transaction_paid', ['reference' => $transaction->reference]);

        return back()->with('status', 'transaction-marked-paid');
    }

    public function confirmSell(Transaction $transaction): RedirectResponse
    {
        if ($transaction->type !== 'sell') {
            return back()->withErrors(['transaction' => 'Only sell orders can be confirmed this way.']);
        }

        $this->trades->confirmSell($transaction, auth()->user());

        Notify::send($transaction->user, new TransactionStatusUpdated($transaction->fresh()));

        ActivityLog::record(auth()->id(), 'admin_confirmed_sell', ['reference' => $transaction->reference]);

        return back()->with('status', 'sell-confirmed');
    }

    public function approveWithdrawal(Transaction $transaction): RedirectResponse
    {
        if ($transaction->type !== 'withdrawal') {
            return back()->withErrors(['transaction' => 'Only withdrawal requests can be approved this way.']);
        }

        if ($transaction->status !== 'pending') {
            return back()->withErrors(['transaction' => 'Only a pending withdrawal can be approved.']);
        }

        try {
            DB::transaction(function () use ($transaction) {
                $locked = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);

                if ($locked->type !== 'withdrawal' || $locked->status !== 'pending') {
                    throw new RuntimeException('Only a pending withdrawal can be approved.');
                }

                $wallet = $locked->wallet_id
                    ? Wallet::query()->lockForUpdate()->find($locked->wallet_id)
                    : null;

                if (! $wallet) {
                    throw new RuntimeException('This withdrawal has no wallet to debit.');
                }

                // Refuse rather than driving the balance negative. A hold
                // that was reserved past the real balance must not be paid.
                if (bccomp((string) $wallet->balance, (string) $locked->amount, 8) < 0) {
                    throw new RuntimeException('This withdrawal is larger than the wallet balance, so it was not approved.');
                }

                $wallet->balance = bcsub((string) $wallet->balance, (string) $locked->amount, 8);
                $remaining = bcsub((string) $wallet->reserved_balance, (string) $locked->amount, 8);
                $wallet->reserved_balance = bccomp($remaining, '0', 8) < 0 ? '0.00000000' : $remaining;
                $wallet->save();

                $locked->update([
                    'status' => 'completed',
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ]);
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['transaction' => $e->getMessage()]);
        }

        Notify::send($transaction->user, new TransactionStatusUpdated($transaction->fresh()));

        ActivityLog::record(auth()->id(), 'admin_approved_withdrawal', ['reference' => $transaction->reference]);

        return back()->with('status', 'withdrawal-approved');
    }

    public function reject(Request $request, Transaction $transaction): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:255']]);

        try {
            DB::transaction(function () use ($transaction, $request) {
                $locked = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);

                if (in_array($locked->status, ['completed', 'failed'], true)) {
                    throw new RuntimeException('This order has already been processed.');
                }

                $this->releaseHeldFunds($locked);

                $locked->update([
                    'status' => 'failed',
                    'admin_note' => $request->input('reason'),
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ]);
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['transaction' => $e->getMessage()]);
        }

        Notify::send($transaction->user, new TransactionStatusUpdated($transaction->fresh()));

        ActivityLog::record(auth()->id(), 'admin_rejected_transaction', ['reference' => $transaction->reference]);

        return back()->with('status', 'transaction-rejected');
    }

    /**
     * Give a pending hold back to the available balance, once.
     * Withdrawals hold their own amount. Wallet-funded sells hold the
     * crypto amount recorded when the order was placed. Older sells with
     * no funding source never took a hold, so there is nothing to release.
     */
    protected function releaseHeldFunds(Transaction $transaction): void
    {
        if ($transaction->type === 'withdrawal' && $transaction->wallet_id) {
            $wallet = Wallet::query()->lockForUpdate()->find($transaction->wallet_id);
            if ($wallet) {
                $this->wallets->releaseReserve($wallet, $transaction->amount);
            }

            return;
        }

        if ($transaction->type === 'sell' && ($transaction->metadata['funding_source'] ?? null) === 'wallet') {
            $walletId = $transaction->metadata['crypto_wallet_id'] ?? null;
            $held = $transaction->metadata['crypto_amount_held'] ?? null;
            $wallet = $walletId ? Wallet::query()->lockForUpdate()->find($walletId) : null;

            if ($wallet && $held !== null) {
                $this->wallets->releaseReserve($wallet, $held);
            }
        }
    }
}
