<x-admin-layout>
    <x-slot name="title">Contact Messages</x-slot>

    <form method="GET" class="mb-4 flex gap-3">
        <select name="status" class="rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white text-sm">
            <option value="">All Statuses</option>
            @foreach (['new', 'read', 'replied'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <x-secondary-button type="submit">Filter</x-secondary-button>
    </form>

    <form method="POST" action="{{ route('admin.contact-messages.bulk') }}" x-data="{ selected: [], allIds: {{ $messages->pluck('id')->toJson() }} }" @submit="if (selected.length === 0) { $event.preventDefault(); alert('Select at least one message first.'); }">
        @csrf

        <div class="mb-3 flex items-center gap-2 flex-wrap">
            <label class="flex items-center gap-2 text-xs font-semibold text-charcoal-500 px-2">
                <input type="checkbox" @change="selected = $event.target.checked ? [...allIds] : []" :checked="selected.length === allIds.length && allIds.length > 0" class="rounded border-charcoal-300 text-brand-600 focus:ring-brand-500">
                Select all
            </label>
            <span class="text-xs text-charcoal-400" x-show="selected.length > 0" x-text="selected.length + ' selected'" style="display:none"></span>
            <div class="flex-1"></div>
            <button type="submit" name="action" value="mark-read" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-charcoal-200 dark:border-charcoal-700 text-charcoal-600 dark:text-charcoal-300 hover:border-brand-400" :disabled="selected.length === 0" :class="selected.length === 0 ? 'opacity-40 cursor-not-allowed' : ''">Mark Read</button>
            <button type="submit" name="action" value="mark-replied" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-charcoal-200 dark:border-charcoal-700 text-charcoal-600 dark:text-charcoal-300 hover:border-brand-400" :disabled="selected.length === 0" :class="selected.length === 0 ? 'opacity-40 cursor-not-allowed' : ''">Mark Replied</button>
            <button type="submit" name="action" value="delete" onclick="return confirm('Delete the selected message(s) permanently?')" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-800 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" :disabled="selected.length === 0" :class="selected.length === 0 ? 'opacity-40 cursor-not-allowed' : ''">Delete Selected</button>
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm divide-y divide-charcoal-100 dark:divide-charcoal-800">
            @forelse ($messages as $message)
                <div class="flex items-center gap-3 px-5 py-4 hover:bg-charcoal-50 dark:hover:bg-charcoal-800">
                    <input type="checkbox" name="ids[]" value="{{ $message->id }}" x-model="selected" class="rounded border-charcoal-300 text-brand-600 focus:ring-brand-500 flex-shrink-0">
                    <a href="{{ route('admin.contact-messages.show', $message) }}" class="flex-1 flex items-center justify-between min-w-0 gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold truncate {{ $message->status === 'new' ? 'text-charcoal-900 dark:text-white' : 'text-charcoal-500' }}">{{ $message->name }} &lt;{{ $message->email }}&gt;</p>
                            <p class="text-xs text-charcoal-400 truncate">{{ $message->subject ?: \Illuminate\Support\Str::limit($message->message, 60) }}</p>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $message->status === 'new' ? 'bg-amber-100 text-amber-700' : ($message->status === 'replied' ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600') }}">{{ ucfirst($message->status) }}</span>
                            <span class="text-xs text-charcoal-400">{{ $message->created_at->diffForHumans() }}</span>
                        </div>
                    </a>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-charcoal-400 text-center">No messages yet.</p>
            @endforelse
        </div>
    </form>

    <div class="mt-4">{{ $messages->links() }}</div>
</x-admin-layout>
