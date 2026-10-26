<?php

namespace App\Services;

use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Implements the platform's single-vendor buy/sell/swap logic. The
 * platform (Ecozeen Swap) is always the counter-party: users never trade
 * against each other.
 */
class TradeService
{
    public function __construct(
        protected RateService $rates,
        protected WalletService $wallets,
        protected ReferralService $referrals,
    ) {}

    /**
     * Buy: user pays fiat and receives crypto, priced off the admin's
     * current SELL rate (the price the platform sells crypto at).
     */
    public function quoteBuy(CryptoAsset $crypto, FiatCurrency $fiat, float $fiatAmount): array
    {
        $rate = $this->rates->activeRate($crypto->id, $fiat->id);

        if (! $rate) {
            throw new RuntimeException("No active rate is set for {$crypto->symbol}/{$fiat->code}.");
        }

        $cryptoAmount = bcdiv((string) $fiatAmount, (string) $rate->sell_rate, $crypto->decimal_places);

        return [
            'rate' => $rate,
            'crypto_amount' => $cryptoAmount,
            'fiat_amount' => $fiatAmount,
        ];
    }

    public function initiateBuy(User $user, CryptoAsset $crypto, FiatCurrency $fiat, float $fiatAmount, string $paymentMethod, array $extraMetadata = []): Transaction
    {
        $quote = $this->quoteBuy($crypto, $fiat, $fiatAmount);
        $usdEquivalent = $this->toUsd($fiatAmount, $fiat);

        // A double-click, a slow network causing the form to be retried, or
        // the browser "back" button resubmitting the page all produce a
        // second identical POST within a second or two of the first — this
        // used to silently create a second pending order for the exact
        // same purchase. Return the existing one instead of creating a
        // duplicate.
        if ($duplicate = $this->findRecentDuplicateOrder($user, 'buy', $crypto->symbol, $quote['crypto_amount'])) {
            return $duplicate;
        }

        $this->assertWithinDailyLimit($user, $usdEquivalent);

        $wallet = $this->wallets->getOrCreateWallet($user, 'crypto', $crypto->symbol);

        return Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'buy',
            'amount' => $quote['crypto_amount'],
            'fee' => 0,
            'currency_code' => $crypto->symbol,
            'status' => 'pending',
            'reference' => Transaction::generateReference('BUY'),
            'metadata' => array_merge([
                'fiat_amount' => $fiatAmount,
                'fiat_currency' => $fiat->code,
                'rate_id' => $quote['rate']->id,
                'rate_used' => (string) $quote['rate']->sell_rate,
                'payment_method' => $paymentMethod,
                'usd_equivalent' => $usdEquivalent,
            ], $extraMetadata),
        ]);
    }

    public function completeBuy(Transaction $transaction, ?User $admin = null): Transaction
    {
        $wasAlreadyCompleted = $transaction->fresh()?->status === 'completed';

        $transaction = DB::transaction(function () use ($transaction, $admin) {
            $transaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($transaction->status === 'completed') {
                return $transaction;
            }

            // The "buy" order row created in initiateBuy() IS the ledger
            // entry for this trade — crediting the wallet via
            // WalletService::credit() here would create a *second*
            // Transaction row for the same event (it always inserts one),
            // which is exactly what showed up as "duplicate" buy orders.
            // Mutate the wallet balance directly instead, the same way
            // WalletController::creditDeposit() does for deposits.
            $wallet = $this->wallets->getOrCreateWallet($transaction->user, 'crypto', $transaction->currency_code);
            $lockedWallet = Wallet::query()->lockForUpdate()->find($wallet->id);
            $lockedWallet->balance = bcadd((string) $lockedWallet->balance, (string) $transaction->amount, 8);
            $lockedWallet->save();

            $transaction->update([
                'wallet_id' => $lockedWallet->id,
                'status' => 'completed',
                'processed_by' => $admin?->id,
                'processed_at' => now(),
            ]);

            return $transaction;
        });

        if (! $wasAlreadyCompleted) {
            $this->referrals->rewardCompletedTrade($transaction);
        }

        return $transaction;
    }

    /**
     * Sell: user sends crypto to the platform's reserve address. The
     * fiat payout (priced off the BUY rate) is only released after an
     * admin confirms the crypto was received.
     */
    public function quoteSell(CryptoAsset $crypto, FiatCurrency $fiat, float $cryptoAmount): array
    {
        $rate = $this->rates->activeRate($crypto->id, $fiat->id);

        if (! $rate) {
            throw new RuntimeException("No active rate is set for {$crypto->symbol}/{$fiat->code}.");
        }

        $fiatAmount = bcmul((string) $cryptoAmount, (string) $rate->buy_rate, 2);

        return [
            'rate' => $rate,
            'crypto_amount' => $cryptoAmount,
            'fiat_amount' => $fiatAmount,
        ];
    }

    public function initiateSell(User $user, CryptoAsset $crypto, FiatCurrency $fiat, float $cryptoAmount, string $depositAddress, array $extraMetadata = []): Transaction
    {
        $quote = $this->quoteSell($crypto, $fiat, $cryptoAmount);
        $usdEquivalent = $this->toUsd((float) $quote['fiat_amount'], $fiat);

        if ($duplicate = $this->findRecentDuplicateOrder($user, 'sell', $fiat->code, $quote['fiat_amount'])) {
            return $duplicate;
        }

        $this->assertWithinDailyLimit($user, $usdEquivalent);

        $wallet = $this->wallets->getOrCreateWallet($user, 'fiat', $fiat->code);

        return Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'sell',
            'amount' => $quote['fiat_amount'],
            'fee' => 0,
            'currency_code' => $fiat->code,
            'status' => 'pending',
            'reference' => Transaction::generateReference('SELL'),
            'metadata' => array_merge([
                'crypto_amount' => $cryptoAmount,
                'crypto_symbol' => $crypto->symbol,
                'rate_id' => $quote['rate']->id,
                'rate_used' => (string) $quote['rate']->buy_rate,
                'deposit_address' => $depositAddress,
                'usd_equivalent' => $usdEquivalent,
            ], $extraMetadata),
        ]);
    }

    public function confirmSell(Transaction $transaction, ?User $admin = null): Transaction
    {
        $wasAlreadyCompleted = $transaction->fresh()?->status === 'completed';

        $transaction = DB::transaction(function () use ($transaction, $admin) {
            $transaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($transaction->status === 'completed') {
                return $transaction;
            }

            // Same reasoning as completeBuy(): the "sell" order row already
            // is the ledger entry, so credit the wallet balance directly
            // instead of inserting a second Transaction via
            // WalletService::credit().
            $wallet = $this->wallets->getOrCreateWallet($transaction->user, 'fiat', $transaction->currency_code);
            $lockedWallet = Wallet::query()->lockForUpdate()->find($wallet->id);
            $lockedWallet->balance = bcadd((string) $lockedWallet->balance, (string) $transaction->amount, 8);
            $lockedWallet->save();

            $transaction->update([
                'wallet_id' => $lockedWallet->id,
                'status' => 'completed',
                'processed_by' => $admin?->id,
                'processed_at' => now(),
            ]);

            return $transaction;
        });

        if (! $wasAlreadyCompleted) {
            $this->referrals->rewardCompletedTrade($transaction);
        }

        return $transaction;
    }

    /**
     * Swap: instant conversion between two crypto assets, priced through
     * USD so the platform's buy/sell spread on each asset is preserved.
     * The quoted rate is locked for 5 minutes via cache.
     */
    public function quoteSwap(User $user, CryptoAsset $from, CryptoAsset $to, float $amount): array
    {
        $usd = FiatCurrency::query()->where('code', 'USD')->firstOrFail();

        $fromRate = $this->rates->activeRate($from->id, $usd->id);
        $toRate = $this->rates->activeRate($to->id, $usd->id);

        if (! $fromRate || ! $toRate) {
            throw new RuntimeException('Swap is unavailable: missing USD rates for one of the selected assets.');
        }

        $usdValue = bcmul((string) $amount, (string) $fromRate->buy_rate, 8);
        $toAmount = bcdiv($usdValue, (string) $toRate->sell_rate, $to->decimal_places);

        $token = 'swap_lock_'.$user->id.'_'.bin2hex(random_bytes(8));

        $lock = [
            'user_id' => $user->id,
            'from_asset_id' => $from->id,
            'to_asset_id' => $to->id,
            'from_amount' => $amount,
            'to_amount' => $toAmount,
            'from_rate_id' => $fromRate->id,
            'to_rate_id' => $toRate->id,
            'usd_value' => round((float) $usdValue, 2),
        ];

        Cache::put($token, $lock, now()->addMinutes(5));

        return array_merge($lock, ['token' => $token, 'expires_at' => now()->addMinutes(5)]);
    }

    public function executeSwap(User $user, string $token): Transaction
    {
        $lock = Cache::get($token);

        if (! $lock || $lock['user_id'] !== $user->id) {
            throw new RuntimeException('This swap quote has expired. Please request a new quote.');
        }

        Cache::forget($token);

        $from = CryptoAsset::findOrFail($lock['from_asset_id']);
        $to = CryptoAsset::findOrFail($lock['to_asset_id']);

        return DB::transaction(function () use ($user, $from, $to, $lock) {
            $fromWallet = $this->wallets->getOrCreateWallet($user, 'crypto', $from->symbol);

            if (! $this->wallets->hasSufficientBalance($fromWallet, $lock['from_amount'])) {
                throw new RuntimeException("Insufficient {$from->symbol} balance to complete this swap.");
            }

            $this->assertWithinDailyLimit($user, (float) $lock['usd_value']);

            $this->wallets->debit($fromWallet, $lock['from_amount'], [
                'type' => 'swap',
                'currency_code' => $from->symbol,
                'status' => 'completed',
                'reference' => Transaction::generateReference('SWAPOUT'),
                'metadata' => ['to_asset' => $to->symbol, 'to_amount' => $lock['to_amount'], 'usd_equivalent' => $lock['usd_value']],
            ]);

            $toWallet = $this->wallets->getOrCreateWallet($user, 'crypto', $to->symbol);
            $transaction = $this->wallets->credit($toWallet, $lock['to_amount'], [
                'type' => 'swap',
                'currency_code' => $to->symbol,
                'status' => 'completed',
                'reference' => Transaction::generateReference('SWAPIN'),
                'metadata' => ['from_asset' => $from->symbol, 'from_amount' => $lock['from_amount'], 'usd_equivalent' => $lock['usd_value']],
            ]);

            return $transaction;
        });
    }

    /**
     * Guards against duplicate order creation from a double-click, a
     * flaky-network form resubmission, or a "back button then resubmit" —
     * all of which send the exact same POST again within a few seconds.
     * Looks for a still-pending order of the same type/amount/currency for
     * this user created in the last 20 seconds and, if found, returns it
     * instead of letting a second identical order through.
     */
    protected function findRecentDuplicateOrder(User $user, string $type, string $currencyCode, string $amount): ?Transaction
    {
        return Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->where('currency_code', $currencyCode)
            ->where('amount', $amount)
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subSeconds(20))
            ->latest()
            ->first();
    }

    public function toUsd(float $amount, FiatCurrency $fiat): float
    {
        return round($amount * (float) $fiat->exchange_rate_to_usd, 2);
    }

    public function assertWithinDailyLimit(User $user, float $usdAmount): void
    {
        if ($user->isKycVerified()) {
            return;
        }

        $limit = (float) $user->daily_trade_limit;

        // "amount" is stored in whatever currency/asset the transaction is
        // denominated in (crypto for buys, fiat for sells...), so we can't
        // sum it directly across types. Each trade records its USD-equivalent
        // value in metadata at creation time specifically for this check.
        $usedToday = Transaction::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['buy', 'sell', 'swap'])
            ->where('created_at', '>=', now()->subDay())
            ->get()
            ->sum(fn (Transaction $transaction) => (float) ($transaction->metadata['usd_equivalent'] ?? 0));

        if (($usedToday + $usdAmount) > $limit) {
            throw new RuntimeException('This trade would exceed your daily trading limit. Complete KYC verification to increase your limit.');
        }
    }
}
