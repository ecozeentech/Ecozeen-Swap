<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ReferralWithdrawal;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ReferralController extends Controller
{
    public function __construct(protected ReferralService $referrals) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('referrals.index', [
            'referralCode' => $user->referral_code,
            'referralLink' => $user->referralLink(),
            'totalReferred' => $user->referredUsers()->count(),
            'totalEarned' => $this->referrals->totalEarned($user),
            'availableBalance' => $this->referrals->availableBalance($user),
            'minWithdrawal' => $this->referrals->minWithdrawal(),
            'commissionRate' => $this->referrals->commissionRate(),
            'signupBonusEnabled' => $this->referrals->signupBonusEnabled(),
            'signupBonusAmount' => $this->referrals->signupBonusAmount(),
            'referredUsers' => $user->referredUsers()->latest()->limit(10)->get(),
            'commissions' => $user->referralCommissions()->with('referredUser')->latest()->limit(15)->get(),
            'withdrawals' => $user->referralWithdrawals()->with('bankAccount')->latest()->limit(10)->get(),
            'bankAccounts' => $user->bankAccounts()->active()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
        ]);

        $user = $request->user();
        $bankAccount = BankAccount::query()->where('user_id', $user->id)->find($request->input('bank_account_id'));

        try {
            $this->referrals->requestWithdrawal($user, (float) $request->input('amount'), $bankAccount);
        } catch (RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        return redirect()->route('referrals.index')->with('status', 'referral-withdrawal-requested');
    }

    public function cancel(Request $request, ReferralWithdrawal $withdrawal): RedirectResponse
    {
        abort_unless($withdrawal->user_id === $request->user()->id, 403);
        abort_unless($withdrawal->status === 'pending', 403);

        $withdrawal->delete();

        return back()->with('status', 'referral-withdrawal-cancelled');
    }
}
