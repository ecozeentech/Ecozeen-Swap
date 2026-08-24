<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\KycDocument;
use App\Models\User;
use App\Notifications\KycStatusUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->input('kyc_status'), fn ($query, $status) => $query->where('kyc_status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    public function show(User $user): View
    {
        $user->load(['wallets', 'kycDocuments', 'giftCards']);

        $transactions = $user->transactions()->latest()->limit(20)->get();
        $activityLogs = $user->activityLogs()->latest()->limit(30)->get();

        return view('admin.users.show', [
            'user' => $user,
            'transactions' => $transactions,
            'activityLogs' => $activityLogs,
        ]);
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        $user->update([
            'is_suspended' => true,
            'suspended_at' => now(),
            'suspension_reason' => $request->input('reason'),
        ]);

        ActivityLog::record(auth()->id(), 'admin_suspended_user', ['user_id' => $user->id]);

        return back()->with('status', 'user-suspended');
    }

    public function unsuspend(User $user): RedirectResponse
    {
        $user->update(['is_suspended' => false, 'suspended_at' => null, 'suspension_reason' => null]);

        ActivityLog::record(auth()->id(), 'admin_unsuspended_user', ['user_id' => $user->id]);

        return back()->with('status', 'user-unsuspended');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'daily_trade_limit' => ['required', 'numeric', 'min:0'],
        ]);

        $user->update(['daily_trade_limit' => $request->input('daily_trade_limit')]);

        ActivityLog::record(auth()->id(), 'admin_updated_user_limit', ['user_id' => $user->id]);

        return back()->with('status', 'user-updated');
    }

    public function approveKyc(KycDocument $kycDocument): RedirectResponse
    {
        $kycDocument->update([
            'status' => 'approved',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $user = $kycDocument->user;
        $hasRejected = $user->kycDocuments()->where('status', 'rejected')->exists();
        $allApproved = ! $user->kycDocuments()->where('status', '!=', 'approved')->exists();

        if ($allApproved && ! $hasRejected) {
            $user->update(['kyc_status' => 'verified', 'daily_trade_limit' => 5000000]);
            $user->notify(new KycStatusUpdated('verified'));
        }

        ActivityLog::record(auth()->id(), 'admin_approved_kyc', ['user_id' => $user->id, 'document_id' => $kycDocument->id]);

        return back()->with('status', 'kyc-approved');
    }

    public function rejectKyc(Request $request, KycDocument $kycDocument): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $kycDocument->update([
            'status' => 'rejected',
            'rejection_reason' => $request->input('reason'),
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $kycDocument->user->update(['kyc_status' => 'rejected']);
        $kycDocument->user->notify(new KycStatusUpdated('rejected', $request->input('reason')));

        ActivityLog::record(auth()->id(), 'admin_rejected_kyc', ['user_id' => $kycDocument->user_id, 'document_id' => $kycDocument->id]);

        return back()->with('status', 'kyc-rejected');
    }
}
