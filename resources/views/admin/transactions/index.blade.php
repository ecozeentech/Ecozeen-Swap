<x-admin-layout>
    <x-slot name="title">Transactions</x-slot>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search reference or username" class="rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500 flex-1 min-w-[200px]">
        <select name="type" class="rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
            <option value="">All Types</option>
            @foreach (['deposit', 'withdrawal', 'buy', 'sell', 'swap', 'fee', 'giftcard', 'invoice'] as $type)
                <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
            <option value="">All Statuses</option>
            @foreach (['pending', 'processing', 'completed', 'failed', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <x-secondary-button type="submit">Filter</x-secondary-button>
    </form>

    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-charcoal-400 text-xs uppercase">
                <tr>
                    <th class="px-5 py-3">Reference</th>
                    <th class="px-5 py-3">User</th>
                    <th class="px-5 py-3">Type</th>
                    <th class="px-5 py-3">Amount</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                @foreach ($transactions as $tx)
                    <tr>
                        <td class="px-5 py-3 font-mono text-xs">{{ $tx->reference }}</td>
                        <td class="px-5 py-3">{{ $tx->user->username ?? 'N/A' }}</td>
                        <td class="px-5 py-3 capitalize">{{ $tx->type }}</td>
                        <td class="px-5 py-3">{{ number_format($tx->amount, 4) }} {{ $tx->currency_code }}</td>
                        <td class="px-5 py-3">
                            <span @class([
                                'text-xs font-medium px-2 py-0.5 rounded-full',
                                'bg-green-100 text-green-700' => $tx->status === 'completed',
                                'bg-amber-100 text-amber-700' => in_array($tx->status, ['pending', 'processing']),
                                'bg-red-100 text-red-700' => in_array($tx->status, ['failed', 'cancelled']),
                            ])>{{ ucfirst($tx->status) }}</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.transactions.show', $tx) }}" class="text-brand-600 font-semibold hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</x-admin-layout>
