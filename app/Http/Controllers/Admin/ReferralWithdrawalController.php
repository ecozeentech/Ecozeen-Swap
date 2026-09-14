<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ReferralWithdrawal;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralWithdrawalController extends Controller
{
    public function __construct(protected ReferralService $referrals) {}

    public function index(Request $request): View
    {
        $withdrawals = ReferralWithdrawal::query()
            ->with(['user', 'bankAccount'])
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status), fn ($q) => $q->whereIn('status', ['pending', 'approved']))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.referrals.withdrawals', ['withdrawals' => $withdrawals]);
    }

    public function approve(Request $request, ReferralWithdrawal $withdrawal): RedirectResponse
    {
        if ($withdrawal->status !== 'pending') {
            return back()->withErrors(['withdrawal' => 'Only pending requests can be approved.']);
        }

        $this->referrals->approveWithdrawal($withdrawal, $request->user());

        ActivityLog::record(auth()->id(), 'admin_approved_referral_withdrawal', ['withdrawal_id' => $withdrawal->id]);

        return back()->with('status', 'referral-withdrawal-approved');
    }

    public function markPaid(Request $request, ReferralWithdrawal $withdrawal): RedirectResponse
    {
        if (! in_array($withdrawal->status, ['pending', 'approved'], true)) {
            return back()->withErrors(['withdrawal' => 'This request cannot be marked paid from its current status.']);
        }

        $this->referrals->markWithdrawalPaid($withdrawal, $request->user());

        ActivityLog::record(auth()->id(), 'admin_paid_referral_withdrawal', ['withdrawal_id' => $withdrawal->id]);

        return back()->with('status', 'referral-withdrawal-paid');
    }

    public function reject(Request $request, ReferralWithdrawal $withdrawal): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:255']]);

        if ($withdrawal->status === 'paid') {
            return back()->withErrors(['withdrawal' => 'A paid withdrawal cannot be rejected.']);
        }

        $this->referrals->rejectWithdrawal($withdrawal, $request->user(), $request->input('reason'));

        ActivityLog::record(auth()->id(), 'admin_rejected_referral_withdrawal', ['withdrawal_id' => $withdrawal->id]);

        return back()->with('status', 'referral-withdrawal-rejected');
    }
}
