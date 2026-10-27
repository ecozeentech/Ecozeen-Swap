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

        return bccomp($available, $this->normalize($amount), 8) >= 0;
    }

    /**
     * Hold funds so they cannot be spent twice. The check and the hold
     * happen on the locked row, so two requests cannot both pass.
     */
    public function reserve(Wallet $wallet, float|string $amount): Wallet
    {
        $amount = $this->normalize($amount);
        $this->assertPositive($amount, 'Hold');

        return DB::transaction(function () use ($wallet, $amount) {
            $locked = $this->locked($wallet);
            $available = bcsub((string) $locked->balance, (string) $locked->reserved_balance, 8);

            if (bccomp($available, $amount, 8) < 0) {
                throw new RuntimeException('Insufficient wallet balance.');
            }

            $locked->reserved_balance = bcadd((string) $locked->reserved_balance, $amount, 8);
            $locked->save();

            return $locked;
        });
    }

    /**
     * Put a hold back into the available balance. Used when a withdrawal
     * or wallet-funded sell is rejected. Floors at zero so a repeated
     * release cannot drive the hold negative.
     */
    public function releaseReserve(Wallet $wallet, float|string $amount): Wallet
    {
        $amount = $this->normalize($amount);

        return DB::transaction(function () use ($wallet, $amount) {
            $locked = $this->locked($wallet);
            $remaining = bcsub((string) $locked->reserved_balance, $amount, 8);
            $locked->reserved_balance = bccomp($remaining, '0', 8) < 0 ? '0.00000000' : $remaining;
            $locked->save();

            return $locked;
        });
    }

    /**
     * Finish a hold: the money has left the platform, so it comes out of
     * both the hold and the balance. Refuses to push the balance below zero.
     */
    public function captureReserve(Wallet $wallet, float|string $amount): Wallet
    {
        $amount = $this->normalize($amount);
        $this->assertPositive($amount, 'Capture');

        return DB::transaction(function () use ($wallet, $amount) {
            $locked = $this->locked($wallet);

            if (bccomp((string) $locked->balance, $amount, 8) < 0) {
                throw new RuntimeException('Wallet balance is lower than the amount being finalized.');
            }

            $locked->balance = bcsub((string) $locked->balance, $amount, 8);
            $remaining = bcsub((string) $locked->reserved_balance, $amount, 8);
            $locked->reserved_balance = bccomp($remaining, '0', 8) < 0 ? '0.00000000' : $remaining;
            $locked->save();

            return $locked;
        });
    }

    protected function locked(Wallet $wallet): Wallet
    {
        $locked = Wallet::query()->lockForUpdate()->find($wallet->id);

        if (! $locked) {
            throw new RuntimeException('Wallet not found.');
        }

        return $locked;
    }

    protected function normalize(float|string $amount): string
    {
        return bcadd(is_string($amount) ? $amount : number_format($amount, 8, '.', ''), '0', 8);
    }

    protected function assertPositive(string $amount, string $action): void
    {
        if (bccomp($amount, '0', 8) <= 0) {
            throw new RuntimeException($action.' amount must be greater than zero.');
        }
    }
}
