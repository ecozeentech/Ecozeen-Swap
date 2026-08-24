<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * All balance mutations flow through this service so every credit/debit
 * happens inside a DB transaction with row-locking to prevent race
 * conditions on concurrent trades.
 */
class WalletService
{
    public function getOrCreateWallet(User $user, string $currencyType, string $currencyCode): Wallet
    {
        return Wallet::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'currency_type' => $currencyType,
                'currency_code' => strtoupper($currencyCode),
            ],
            ['balance' => 0, 'reserved_balance' => 0]
        );
    }

    public function credit(Wallet $wallet, float|string $amount, array $transactionAttributes = []): Transaction
    {
        if (bccomp((string) $amount, '0', 8) <= 0) {
            throw new RuntimeException('Credit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($wallet, $amount, $transactionAttributes) {
            $locked = Wallet::query()->lockForUpdate()->find($wallet->id);
            $locked->balance = bcadd((string) $locked->balance, (string) $amount, 8);
            $locked->save();

            return Transaction::create(array_merge([
                'user_id' => $locked->user_id,
                'wallet_id' => $locked->id,
                'amount' => $amount,
                'currency_code' => $locked->currency_code,
                'status' => 'completed',
                'reference' => Transaction::generateReference(),
                'processed_at' => now(),
            ], $transactionAttributes));
        });
    }

    public function debit(Wallet $wallet, float|string $amount, array $transactionAttributes = []): Transaction
    {
        if (bccomp((string) $amount, '0', 8) <= 0) {
            throw new RuntimeException('Debit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($wallet, $amount, $transactionAttributes) {
            $locked = Wallet::query()->lockForUpdate()->find($wallet->id);

            $available = bcsub((string) $locked->balance, (string) $locked->reserved_balance, 8);
            if (bccomp($available, (string) $amount, 8) < 0) {
                throw new RuntimeException('Insufficient wallet balance.');
            }

            $locked->balance = bcsub((string) $locked->balance, (string) $amount, 8);
            $locked->save();

            return Transaction::create(array_merge([
                'user_id' => $locked->user_id,
                'wallet_id' => $locked->id,
                'amount' => $amount,
                'currency_code' => $locked->currency_code,
                'status' => 'completed',
                'reference' => Transaction::generateReference(),
                'processed_at' => now(),
            ], $transactionAttributes));
        });
    }

    public function hasSufficientBalance(Wallet $wallet, float|string $amount): bool
    {
        $available = bcsub((string) $wallet->balance, (string) $wallet->reserved_balance, 8);

        return bccomp($available, (string) $amount, 8) >= 0;
    }
}
