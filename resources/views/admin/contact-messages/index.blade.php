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

    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm divide-y divide-charcoal-100 dark:divide-charcoal-800">
        @forelse ($messages as $message)
            <a href="{{ route('admin.contact-messages.show', $message) }}" class="flex items-center justify-between px-5 py-4 hover:bg-charcoal-50 dark:hover:bg-charcoal-800">
                <div>
                    <p class="font-semibold {{ $message->status === 'new' ? 'text-charcoal-900 dark:text-white' : 'text-charcoal-500' }}">{{ $message->name }} &lt;{{ $message->email }}&gt;</p>
                    <p class="text-xs text-charcoal-400">{{ $message->subject ?: \Illuminate\Support\Str::limit($message->message, 60) }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $message->status === 'new' ? 'bg-amber-100 text-amber-700' : ($message->status === 'replied' ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600') }}">{{ ucfirst($message->status) }}</span>
                    <span class="text-xs text-charcoal-400">{{ $message->created_at->diffForHumans() }}</span>
                </div>
            </a>
        @empty
            <p class="px-5 py-6 text-sm text-charcoal-400 text-center">No messages yet.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $messages->links() }}</div>
</x-admin-layout>
