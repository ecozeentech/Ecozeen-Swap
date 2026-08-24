<div wire:poll.60s="$refresh" class="space-y-6">
    <div class="rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 text-white p-6 shadow-lg">
        <p class="text-sm text-brand-100">Total Portfolio Value</p>
        <p class="text-3xl sm:text-4xl font-extrabold mt-1">${{ number_format($portfolioUsd, 2) }}</p>
        <p class="text-xs text-brand-100 mt-2">Estimated USD equivalent across all wallets</p>
    </div>

    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800">
            <h3 class="font-semibold text-charcoal-900 dark:text-white">Recent Activity</h3>
            <a href="{{ route('wallet.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
            @forelse ($recentTransactions as $tx)
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-sm font-semibold text-charcoal-900 dark:text-white capitalize">{{ $tx->type }} &middot; {{ $tx->currency_code }}</p>
                        <p class="text-xs text-charcoal-400">{{ $tx->created_at->diffForHumans() }} &middot; {{ $tx->reference }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-charcoal-900 dark:text-white">{{ number_format($tx->amount, 4) }}</p>
                        <span @class([
                            'text-xs font-medium px-2 py-0.5 rounded-full',
                            'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' => $tx->status === 'completed',
                            'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' => in_array($tx->status, ['pending', 'processing']),
                            'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => in_array($tx->status, ['failed', 'cancelled']),
                        ])>{{ ucfirst($tx->status) }}</span>
                    </div>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-charcoal-400 text-center">No transactions yet. Start by funding your wallet.</p>
            @endforelse
        </div>
    </div>
</div>
