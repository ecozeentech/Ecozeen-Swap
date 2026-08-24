<x-admin-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['Total Users', $stats['total_users'], 'brand'],
            ['New Today', $stats['new_users_today'], 'brand'],
            ['Total Transactions', $stats['total_transactions'], 'brand'],
            ['Pending Transactions', $stats['pending_transactions'], 'amber'],
            ['Completed Volume', number_format($stats['completed_volume_usd'], 2), 'brand'],
            ['Pending KYC', $stats['pending_kyc'], 'amber'],
            ['Pending Gift Cards', $stats['pending_giftcards'], 'amber'],
            ['Suspended Users', $stats['suspended_users'], 'red'],
        ] as [$label, $value, $color])
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-5 shadow-sm">
                <p class="text-xs text-charcoal-400">{{ $label }}</p>
                <p class="text-2xl font-extrabold mt-1 {{ $color === 'amber' ? 'text-amber-500' : ($color === 'red' ? 'text-red-500' : 'text-charcoal-900 dark:text-white') }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
            <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800 flex items-center justify-between">
                <h3 class="font-semibold">Recent Transactions</h3>
                <a href="{{ route('admin.transactions.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                @foreach ($recentTransactions as $tx)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold">{{ $tx->user->username ?? 'N/A' }} &middot; <span class="capitalize">{{ $tx->type }}</span></p>
                            <p class="text-xs text-charcoal-400">{{ $tx->reference }}</p>
                        </div>
                        <span class="text-sm font-semibold">{{ number_format($tx->amount, 4) }} {{ $tx->currency_code }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
            <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800 flex items-center justify-between">
                <h3 class="font-semibold">Newest Users</h3>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                @foreach ($recentUsers as $user)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold">{{ $user->name }}</p>
                            <p class="text-xs text-charcoal-400">&commat;{{ $user->username }}</p>
                        </div>
                        <span class="text-xs text-charcoal-400">{{ $user->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-admin-layout>
