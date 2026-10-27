<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

/**
 * One path for "this payment is real, credit the user once".
 *
 * Paystack and Flutterwave both call us from the browser redirect and
 * from the webhook, often at the same time. Crediting in either place
 * without locking the order row paid the user twice — naira higher than
 * the deposit, or a buy's crypto amount written into a second wallet.
 */
class PaymentSettlement
{
    public function __construct(
        protected WalletService $wallets,
        protected TradeService $trades,
    ) {}

    public function settle(Transaction $transaction, ?User $admin = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $admin) {
            $locked = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($locked->status === 'completed') {
                return $locked;
            }

            if ($locked->type === 'buy') {
                return $this->trades->completeBuy($locked, $admin);
            }

            if ($locked->type === 'deposit') {
                return $this->creditDeposit($locked, $admin);
            }

            return $locked;
        });
    }

    protected function creditDeposit(Transaction $transaction, ?User $admin): Transaction
    {
        $wallet = $this->wallets->getOrCreateWallet($transaction->user, 'fiat', $transaction->currency_code);
        $lockedWallet = Wallet::query()->lockForUpdate()->findOrFail($wallet->id);
        $lockedWallet->balance = bcadd((string) $lockedWallet->balance, (string) $transaction->amount, 8);
        $lockedWallet->save();

        $transaction->update([
            'wallet_id' => $lockedWallet->id,
            'status' => 'completed',
            'processed_by' => $admin?->id ?? $transaction->processed_by,
            'processed_at' => now(),
        ]);

        return $transaction;
    }
}
