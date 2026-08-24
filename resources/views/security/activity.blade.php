<x-app-layout>
    <x-slot name="title">Activity Log</x-slot>

    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
        <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800">
            <h2 class="text-lg font-bold text-charcoal-900 dark:text-white">Login &amp; Activity History</h2>
        </div>
        <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
            @forelse ($logs as $log)
                <div class="px-5 py-3 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold capitalize">{{ str_replace('_', ' ', $log->action) }}</p>
                        <p class="text-xs text-charcoal-400">{{ $log->ip_address }} &middot; {{ $log->created_at->diffForHumans() }}</p>
                    </div>
                    <p class="text-xs text-charcoal-400 text-right max-w-xs truncate">{{ $log->user_agent }}</p>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-charcoal-400 text-center">No activity recorded yet.</p>
            @endforelse
        </div>
        <div class="px-5 py-3 border-t border-charcoal-100 dark:border-charcoal-800">
            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>
