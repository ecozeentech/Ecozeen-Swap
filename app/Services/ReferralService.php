<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\ReferralCommission;
use App\Models\ReferralWithdrawal;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\AccountNotification;
use App\Notifications\AdminAlert;
use App\Support\Features;
use App\Support\Notify;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Encapsulates the entire referral program: settings, commission
 * crediting, balance accounting, and the withdrawal lifecycle. The
 * program is fully admin-managed — every number here (commission rate,
 * signup bonus, minimum withdrawal) comes from SystemSetting and is
 * editable from Admin > Referral Program with no code changes.
 */
class ReferralService
{
    public const DEFAULT_COMMISSION_RATE = 0.5; // % of trade USD volume

    public const DEFAULT_MIN_WITHDRAWAL = 20.0; // USD

    public const DEFAULT_SIGNUP_BONUS = 2.0; // USD

    public function commissionRate(): float
    {
        return (float) SystemSetting::get('referral_commission_rate', self::DEFAULT_COMMISSION_RATE);
    }

    public function minWithdrawal(): float
    {
        return (float) SystemSetting::get('referral_min_withdrawal', self::DEFAULT_MIN_WITHDRAWAL);
    }

    public function signupBonusEnabled(): bool
    {
        return (bool) SystemSetting::get('referral_signup_bonus_enabled', false);
    }

    public function signupBonusAmount(): float
    {
        return (float) SystemSetting::get('referral_signup_bonus_amount', self::DEFAULT_SIGNUP_BONUS);
    }

    /**
     * Called once a buy/sell transaction is completed. Credits the
     * referrer (if any) their trade commission, and — the first time the
     * referred user ever completes a trade — the one-off signup bonus.
     */
    public function rewardCompletedTrade(Transaction $transaction): void
    {
        $user = $transaction->user;

        if (! $user || ! $user->referred_by || ! Features::isEnabled(Features::REFERRALS)) {
            return;
        }

        $referrer = $user->referrer;

        if (! $referrer) {
            return;
        }

        $usdVolume = (float) ($transaction->metadata['usd_equivalent'] ?? 0);

        if ($usdVolume <= 0) {
            return;
        }

        $isFirstCompletedTrade = ! $this->hasEarnedSignupBonusFor($user);

        DB::transaction(function () use ($referrer, $user, $transaction, $usdVolume, $isFirstCompletedTrade) {
            $rate = $this->commissionRate();

            if ($rate > 0) {
                $commissionAmount = round($usdVolume * ($rate / 100), 2);

                if ($commissionAmount > 0) {
                    ReferralCommission::create([
                        'referrer_id' => $referrer->id,
                        'referred_user_id' => $user->id,
                        'transaction_id' => $transaction->id,
                        'type' => 'trade_commission',
                        'amount' => $commissionAmount,
                        'rate_applied' => $rate,
                        'description' => ucfirst($transaction->type)." by @{$user->username} — {$transaction->reference}",
                    ]);
                }
            }

            if ($isFirstCompletedTrade && $this->signupBonusEnabled()) {
                $bonus = $this->signupBonusAmount();

                if ($bonus > 0) {
                    ReferralCommission::create([
                        'referrer_id' => $referrer->id,
                        'referred_user_id' => $user->id,
                        'transaction_id' => $transaction->id,
                        'type' => 'signup_bonus',
                        'amount' => $bonus,
                        'description' => "Signup bonus — @{$user->username}'s first completed trade",
                    ]);
                }
            }
        });

        Notify::send($referrer, new AccountNotification(
            'Referral Commission Earned',
            "You earned a commission from @{$user->username}'s {$transaction->type}. Check your referral balance.",
            'success',
            route('referrals.index')
        ));
    }

    protected function hasEarnedSignupBonusFor(User $referredUser): bool
    {
        return ReferralCommission::query()
            ->where('referred_user_id', $referredUser->id)
            ->where('type', 'signup_bonus')
            ->exists();
    }

    /**
     * Total ever earned (excluding reversed entries).
     */
    public function totalEarned(User $referrer): float
    {
        return (float) ReferralCommission::query()->where('referrer_id', $referrer->id)->active()->sum('amount');
    }

    /**
     * Amount currently reserved by non-rejected withdrawal requests.
     */
    public function totalReserved(User $referrer): float
    {
        return (float) ReferralWithdrawal::query()->where('user_id', $referrer->id)->reserving()->sum('amount');
    }

    /**
     * What the user can actually request to withdraw right now.
     */
    public function availableBalance(User $referrer): float
    {
        return max(0, round($this->totalEarned($referrer) - $this->totalReserved($referrer), 2));
    }

    /**
     * @return Collection<int, ReferralWithdrawal>
     */
    public function requestWithdrawal(User $user, float $amount, ?BankAccount $bankAccount): ReferralWithdrawal
    {
        $amount = round($amount, 2);
        $min = $this->minWithdrawal();

        if ($amount < $min) {
            throw new RuntimeException('The minimum referral withdrawal amount is $'.number_format($min, 2).'.');
        }

        $available = $this->availableBalance($user);

        if ($amount > $available) {
            throw new RuntimeException('You only have $'.number_format($available, 2).' available to withdraw.');
        }

        if (! $bankAccount || $bankAccount->user_id !== $user->id) {
            throw new RuntimeException('Please select a valid bank account to receive this withdrawal.');
        }

        $withdrawal = ReferralWithdrawal::create([
            'user_id' => $user->id,
            'bank_account_id' => $bankAccount->id,
            'amount' => $amount,
            'status' => 'pending',
            'reference' => ReferralWithdrawal::generateReference(),
        ]);

        AdminAlert::broadcast(
            'Referral Withdrawal Requested',
            "{$user->name} (@{$user->username}) requested a referral commission withdrawal of $".number_format($amount, 2).'.',
            'warning',
            route('admin.referrals.withdrawals')
        );

        return $withdrawal;
    }

    public function approveWithdrawal(ReferralWithdrawal $withdrawal, User $admin): ReferralWithdrawal
    {
        $withdrawal->update([
            'status' => 'approved',
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        return $withdrawal;
    }

    public function markWithdrawalPaid(ReferralWithdrawal $withdrawal, User $admin): ReferralWithdrawal
    {
        $withdrawal->update([
            'status' => 'paid',
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        Notify::send($withdrawal->user, new AccountNotification(
            'Referral Withdrawal Paid',
            'Your referral commission withdrawal of $'.number_format((float) $withdrawal->amount, 2).' has been paid out.',
            'success',
            route('referrals.index')
        ));

        return $withdrawal;
    }

    public function rejectWithdrawal(ReferralWithdrawal $withdrawal, User $admin, string $reason): ReferralWithdrawal
    {
        $withdrawal->update([
            'status' => 'rejected',
            'admin_note' => $reason,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        Notify::send($withdrawal->user, new AccountNotification(
            'Referral Withdrawal Rejected',
            'Your referral withdrawal request of $'.number_format((float) $withdrawal->amount, 2)." was rejected: {$reason}",
            'danger',
            route('referrals.index')
        ));

        return $withdrawal;
    }

    public function reverseCommission(ReferralCommission $commission, User $admin): ReferralCommission
    {
        $commission->update([
            'is_reversed' => true,
            'reversed_by' => $admin->id,
            'reversed_at' => now(),
        ]);

        return $commission;
    }
}
