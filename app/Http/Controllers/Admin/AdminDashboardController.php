<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Models\KycDocument;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::query()->count(),
            'new_users_today' => User::query()->whereDate('created_at', today())->count(),
            'total_transactions' => Transaction::query()->count(),
            'pending_transactions' => Transaction::query()->where('status', 'pending')->count(),
            'completed_volume_usd' => Transaction::query()->where('status', 'completed')->sum('amount'),
            'pending_kyc' => KycDocument::query()->where('status', 'pending')->count(),
            'pending_giftcards' => GiftCard::query()->where('status', 'pending')->count(),
            'suspended_users' => User::query()->where('is_suspended', true)->count(),
        ];

        $recentTransactions = Transaction::query()->with('user')->latest()->limit(10)->get();
        $recentUsers = User::query()->latest()->limit(5)->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'recentUsers' => $recentUsers,
        ]);
    }
}
