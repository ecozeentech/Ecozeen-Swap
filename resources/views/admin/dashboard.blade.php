<x-admin-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['Total Users', $stats['total_users'], 'brand', route('admin.users.index')],
            ['New Today', $stats['new_users_today'], 'brand', route('admin.users.index')],
            ['Total Orders', $stats['total_transactions'], 'brand', route('admin.transactions.index')],
            ['Pending Orders', $stats['pending_transactions'], 'amber', route('admin.transactions.index', ['status' => 'pending'])],
            ['Pending KYC', $stats['pending_kyc'], 'amber', route('admin.users.index', ['kyc_status' => 'pending'])],
            ['Pending Gift Cards', $stats['pending_giftcards'], 'amber', route('admin.giftcards.index', ['status' => 'pending'])],
            ['Suspended Users', $stats['suspended_users'], 'red', route('admin.users.index', ['suspended' => 1])],
        ] as [$label, $value, $color, $href])
            <a href="{{ $href }}" class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-5 shadow-sm hover:shadow-md hover:border-brand-300 dark:hover:border-brand-700 transition group">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-charcoal-400">{{ $label }}</p>
                    <svg class="h-3.5 w-3.5 text-charcoal-300 group-hover:text-brand-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </div>
                <p class="text-2xl font-extrabold mt-1 {{ $color === 'amber' ? 'text-amber-500' : ($color === 'red' ? 'text-red-500' : 'text-charcoal-900 dark:text-white') }}">{{ $value }}</p>
            </a>
        @endforeach
    </div>

    {{-- Cumulative platform volume, correctly totalled in USD-equivalent
         terms across every currency instead of blindly summing raw
         "amount" values from different currencies together. --}}
    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm mb-6">
        <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800 flex items-center justify-between">
            <h3 class="font-semibold">Platform Volume (All-Time, USD-Equivalent)</h3>
            <span class="text-xs text-charcoal-400">Completed transactions only</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 divide-x divide-y sm:divide-y-0 divide-charcoal-100 dark:divide-charcoal-800">
            @foreach ([
                ['Total Volume', $volumeByType['all'], 'all', 'text-charcoal-900 dark:text-white'],
                ['Buys', $volumeByType['buy'], 'buy', 'text-green-600'],
                ['Sells', $volumeByType['sell'], 'sell', 'text-red-500'],
                ['Deposits', $volumeByType['deposit'], 'deposit', 'text-blue-600'],
                ['Withdrawals', $volumeByType['withdrawal'], 'withdrawal', 'text-amber-600'],
                ['Swaps + Gift Cards', $volumeByType['swap'] + $volumeByType['giftcard'], 'other', 'text-purple-600'],
            ] as [$label, $amount, $filterType, $color])
                <a href="{{ $filterType === 'all' || $filterType === 'other' ? route('admin.transactions.index', ['status' => 'completed']) : route('admin.transactions.index', ['type' => $filterType, 'status' => 'completed']) }}" class="p-5 hover:bg-charcoal-50 dark:hover:bg-charcoal-800/50 transition">
                    <p class="text-xs text-charcoal-400">{{ $label }}</p>
                    <p class="text-lg font-extrabold mt-1 {{ $color }}">${{ number_format($amount, 2) }}</p>
                </a>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
            <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800 flex items-center justify-between">
                <h3 class="font-semibold">Recent Transactions</h3>
                <a href="{{ route('admin.transactions.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                @foreach ($recentTransactions as $tx)
                    <a href="{{ route('admin.transactions.show', $tx) }}" class="px-5 py-3 flex items-center justify-between hover:bg-charcoal-50 dark:hover:bg-charcoal-800/50 transition">
                        <div>
                            <p class="text-sm font-semibold">{{ $tx->user->username ?? 'N/A' }} &middot; <span class="capitalize">{{ $tx->type }}</span></p>
                            <p class="text-xs text-charcoal-400">{{ $tx->reference }}</p>
                        </div>
                        <span class="text-sm font-semibold">{{ number_format($tx->amount, 4) }} {{ $tx->currency_code }}</span>
                    </a>
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
                    <a href="{{ route('admin.users.show', $user) }}" class="px-5 py-3 flex items-center justify-between hover:bg-charcoal-50 dark:hover:bg-charcoal-800/50 transition">
                        <div>
                            <p class="text-sm font-semibold">{{ $user->name }}</p>
                            <p class="text-xs text-charcoal-400">&commat;{{ $user->username }}</p>
                        </div>
                        <span class="text-xs text-charcoal-400">{{ $user->created_at->diffForHumans() }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-admin-layout>
