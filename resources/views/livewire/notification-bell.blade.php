<div x-data="{ open: false }" class="relative" wire:poll.30s>
    <button type="button" @click="open = !open" class="relative rounded-full p-2 text-charcoal-500 hover:bg-charcoal-100 dark:hover:bg-charcoal-800" aria-label="Notifications">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
        @if ($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 h-4 min-w-[16px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center leading-none">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        style="display:none"
        class="absolute right-0 mt-2 w-80 sm:w-96 max-h-[28rem] overflow-y-auto rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-xl z-50"
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-charcoal-100 dark:border-charcoal-800">
            <h3 class="font-semibold text-sm text-charcoal-900 dark:text-white">Notifications</h3>
            @if ($unreadCount > 0)
                <button type="button" wire:click="markAllAsRead" class="text-xs font-semibold text-brand-600 hover:underline">Mark all read</button>
            @endif
        </div>

        <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = $notification->read_at === null;
                @endphp
                <a
                    href="{{ $data['url'] ?? '#' }}"
                    wire:click="markAsRead('{{ $notification->id }}')"
                    class="flex items-start gap-3 px-4 py-3 hover:bg-charcoal-50 dark:hover:bg-charcoal-800/60 {{ $isUnread ? 'bg-brand-50/50 dark:bg-charcoal-800/30' : '' }}"
                >
                    <span @class([
                        'h-8 w-8 flex-shrink-0 rounded-full flex items-center justify-center',
                        'bg-green-100 text-green-600 dark:bg-green-900/40' => ($data['level'] ?? 'info') === 'success',
                        'bg-red-100 text-red-600 dark:bg-red-900/40' => ($data['level'] ?? 'info') === 'danger',
                        'bg-amber-100 text-amber-600 dark:bg-amber-900/40' => ($data['level'] ?? 'info') === 'warning',
                        'bg-brand-100 text-brand-600 dark:bg-charcoal-700' => ($data['level'] ?? 'info') === 'info',
                    ])>
                        @if (($data['level'] ?? 'info') === 'success')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        @elseif (($data['level'] ?? 'info') === 'danger')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        @elseif (($data['level'] ?? 'info') === 'warning')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @else
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @endif
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-charcoal-900 dark:text-white">{{ $data['title'] ?? 'Notification' }}</p>
                        <p class="text-xs text-charcoal-500 dark:text-charcoal-400 mt-0.5 line-clamp-2">{{ $data['message'] ?? '' }}</p>
                        <p class="text-[11px] text-charcoal-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if ($isUnread)
                        <span class="h-2 w-2 rounded-full bg-brand-500 flex-shrink-0 mt-1.5"></span>
                    @endif
                </a>
            @empty
                <p class="px-4 py-8 text-sm text-charcoal-400 text-center">No notifications yet.</p>
            @endforelse
        </div>
    </div>
</div>
