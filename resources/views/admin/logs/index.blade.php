<x-admin-layout>
    <x-slot name="title">System Logs</x-slot>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action" class="rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500 flex-1 min-w-[200px]">
        <x-secondary-button type="submit">Filter</x-secondary-button>
    </form>

    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-charcoal-400 text-xs uppercase">
                <tr>
                    <th class="px-5 py-3">User</th>
                    <th class="px-5 py-3">Action</th>
                    <th class="px-5 py-3">IP</th>
                    <th class="px-5 py-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                @foreach ($logs as $log)
                    <tr>
                        <td class="px-5 py-3">{{ $log->user->username ?? 'System' }}</td>
                        <td class="px-5 py-3 capitalize">{{ str_replace('_', ' ', $log->action) }}</td>
                        <td class="px-5 py-3 font-mono text-xs">{{ $log->ip_address }}</td>
                        <td class="px-5 py-3 text-charcoal-400">{{ $log->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</x-admin-layout>
