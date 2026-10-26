<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Transaction;
use App\Notifications\TransactionStatusUpdated;
use App\Services\TradeService;
use App\Services\WalletService;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(protected TradeService $trades, protected WalletService $wallets) {}

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

        DB::transaction(function () use ($transaction) {
            if ($transaction->type === 'buy') {
                $this->trades->completeBuy($transaction, auth()->user());
            } else {
                // Deposits can also be confirmed via WalletController::creditDeposit()
                // (gateway webhook/callback); this is the manual bank-transfer path.
                $locked = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);

                if ($locked->status !== 'completed') {
                    $wallet = $this->wallets->getOrCreateWallet($locked->user, 'fiat', $locked->currency_code);
                    $wallet->increment('balance', $locked->amount);
                    $locked->update(['status' => 'completed', 'processed_by' => auth()->id(), 'processed_at' => now()]);
                }
            }
        });

        Notify::send($transaction->user, new TransactionStatusUpdated($transaction->fresh()));

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

        DB::transaction(function () use ($transaction) {
            $wallet = $transaction->wallet()->lockForUpdate()->first() ?? $this->wallets->getOrCreateWallet($transaction->user, 'fiat', $transaction->currency_code);

            // The funds were already held in reserved_balance the moment the
            // withdrawal was requested (so the user couldn't spend them
            // twice). Approving finalizes it: release the hold AND take the
            // money out of the wallet for good, since it has now actually
            // left the platform.
            $wallet->reserved_balance = max(0, bcsub((string) $wallet->reserved_balance, (string) $transaction->amount, 8));
            $wallet->balance = bcsub((string) $wallet->balance, (string) $transaction->amount, 8);
            $wallet->save();

            $transaction->update([
                'status' => 'completed',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);
        });

        Notify::send($transaction->user, new TransactionStatusUpdated($transaction->fresh()));

        ActivityLog::record(auth()->id(), 'admin_approved_withdrawal', ['reference' => $transaction->reference]);

        return back()->with('status', 'withdrawal-approved');
    }

    public function reject(Request $request, Transaction $transaction): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:255']]);

        DB::transaction(function () use ($transaction, $request) {
            if ($transaction->type === 'withdrawal') {
                $wallet = $transaction->wallet;
                if ($wallet) {
                    $wallet->reserved_balance = max(0, bcsub((string) $wallet->reserved_balance, (string) $transaction->amount, 8));
                    $wallet->save();
                }
            }

            $transaction->update([
                'status' => 'failed',
                'admin_note' => $request->input('reason'),
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);
        });

        Notify::send($transaction->user, new TransactionStatusUpdated($transaction->fresh()));

        ActivityLog::record(auth()->id(), 'admin_rejected_transaction', ['reference' => $transaction->reference]);

        return back()->with('status', 'transaction-rejected');
    }
}
