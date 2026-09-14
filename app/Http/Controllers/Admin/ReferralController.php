<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ReferralCommission;
use App\Models\ReferralWithdrawal;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Full admin control over the referral program: the commission rate,
 * signup bonus, minimum withdrawal threshold, a leaderboard of referrers,
 * the complete commission ledger, and the ability to reverse a bad entry.
 * Withdrawal requests themselves live under Admin\ReferralWithdrawalController.
 */
class ReferralController extends Controller
{
    public function __construct(protected ReferralService $referrals) {}

    public function index(Request $request): View
    {
        $topReferrers = User::query()
            ->withCount('referredUsers')
            ->having('referred_users_count', '>', 0)
            ->orderByDesc('referred_users_count')
            ->limit(10)
            ->get()
            ->map(fn (User $user) => [
                'user' => $user,
                'earned' => $this->referrals->totalEarned($user),
                'available' => $this->referrals->availableBalance($user),
            ]);

        return view('admin.referrals.index', [
            'settings' => SystemSetting::allSettings(),
            'stats' => [
                'total_referrers' => User::query()->has('referredUsers')->count(),
                'total_referred_users' => User::query()->whereNotNull('referred_by')->count(),
                'total_paid_commissions' => (float) ReferralCommission::query()->active()->sum('amount'),
                'pending_withdrawals' => ReferralWithdrawal::query()->where('status', 'pending')->count(),
                'pending_withdrawal_amount' => (float) ReferralWithdrawal::query()->where('status', 'pending')->sum('amount'),
            ],
            'topReferrers' => $topReferrers,
            'commissions' => ReferralCommission::query()->with(['referrer', 'referredUser'])->latest()->limit(30)->get(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'referral_commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'referral_min_withdrawal' => ['required', 'numeric', 'min:0'],
            'referral_signup_bonus_enabled' => ['nullable', 'boolean'],
            'referral_signup_bonus_amount' => ['required_if:referral_signup_bonus_enabled,1', 'nullable', 'numeric', 'min:0'],
        ]);

        SystemSetting::set('referral_commission_rate', $request->input('referral_commission_rate'), 'string', 'referral');
        SystemSetting::set('referral_min_withdrawal', $request->input('referral_min_withdrawal'), 'string', 'referral');
        SystemSetting::set('referral_signup_bonus_enabled', $request->boolean('referral_signup_bonus_enabled'), 'boolean', 'referral');
        SystemSetting::set('referral_signup_bonus_amount', $request->input('referral_signup_bonus_amount', 0), 'string', 'referral');

        ActivityLog::record(auth()->id(), 'admin_updated_referral_settings');

        return back()->with('status', 'referral-settings-updated');
    }

    public function reverseCommission(Request $request, ReferralCommission $commission): RedirectResponse
    {
        if ($commission->is_reversed) {
            return back()->withErrors(['commission' => 'This commission has already been reversed.']);
        }

        $this->referrals->reverseCommission($commission, $request->user());

        ActivityLog::record(auth()->id(), 'admin_reversed_referral_commission', ['commission_id' => $commission->id]);

        return back()->with('status', 'referral-commission-reversed');
    }
}
