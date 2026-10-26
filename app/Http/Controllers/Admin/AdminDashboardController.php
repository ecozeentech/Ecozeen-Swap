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
            'pending_kyc' => KycDocument::query()->where('status', 'pending')->count(),
            'pending_giftcards' => GiftCard::query()->where('status', 'pending')->count(),
            'suspended_users' => User::query()->where('is_suspended', true)->count(),
        ];

        $volumeByType = $this->cumulativeVolumeByType();

        $recentTransactions = Transaction::query()->with('user')->latest()->limit(10)->get();
        $recentUsers = User::query()->latest()->limit(5)->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'volumeByType' => $volumeByType,
            'recentTransactions' => $recentTransactions,
            'recentUsers' => $recentUsers,
        ]);
    }

    /**
     * Platform-wide totals in USD-equivalent terms, correctly summed
     * across every currency a transaction might be denominated in
     * (previously this just summed the raw "amount" column across BTC,
     * NGN, USD, etc. indiscriminately, which is meaningless). Only
     * completed transactions count toward these totals.
     *
     * @return array<string, float>
     */
    protected function cumulativeVolumeByType(): array
    {
        $totals = ['buy' => 0.0, 'sell' => 0.0, 'deposit' => 0.0, 'withdrawal' => 0.0, 'swap' => 0.0, 'giftcard' => 0.0];

        Transaction::query()
            ->where('status', 'completed')
            ->whereIn('type', array_keys($totals))
            ->select(['type', 'amount', 'currency_code', 'metadata'])
            ->chunk(500, function ($transactions) use (&$totals) {
                foreach ($transactions as $transaction) {
                    $totals[$transaction->type] = ($totals[$transaction->type] ?? 0) + $transaction->usdEquivalent();
                }
            });

        $totals['all'] = array_sum($totals);

        return $totals;
    }
}
