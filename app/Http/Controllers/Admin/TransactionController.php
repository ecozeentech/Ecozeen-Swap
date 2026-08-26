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
        DB::transaction(function () use ($transaction) {
            if ($transaction->type === 'buy') {
                $this->trades->completeBuy($transaction, auth()->user());
            } else {
                $wallet = $this->wallets->getOrCreateWallet($transaction->user, 'fiat', $transaction->currency_code);
                $wallet->increment('balance', $transaction->amount);
                $transaction->update(['status' => 'completed', 'processed_by' => auth()->id(), 'processed_at' => now()]);
            }
        });

        Notify::send($transaction->user, new TransactionStatusUpdated($transaction->fresh()));

        ActivityLog::record(auth()->id(), 'admin_marked_transaction_paid', ['reference' => $transaction->reference]);

        return back()->with('status', 'transaction-marked-paid');
    }

    public function confirmSell(Transaction $transaction): RedirectResponse
    {
        $this->trades->confirmSell($transaction, auth()->user());

        Notify::send($transaction->user, new TransactionStatusUpdated($transaction->fresh()));

        ActivityLog::record(auth()->id(), 'admin_confirmed_sell', ['reference' => $transaction->reference]);

        return back()->with('status', 'sell-confirmed');
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
