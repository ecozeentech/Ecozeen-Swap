<x-admin-layout>
    <x-slot name="title">Message from {{ $message->name }}</x-slot>

    <div class="max-w-2xl rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="font-semibold text-lg">{{ $message->name }}</p>
                <p class="text-sm text-charcoal-400">{{ $message->email }} &middot; {{ $message->created_at->format('M d, Y H:i') }}</p>
            </div>
            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $message->status === 'replied' ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600' }}">{{ ucfirst($message->status) }}</span>
        </div>

        @if ($message->subject)
            <p class="font-semibold mb-2">{{ $message->subject }}</p>
        @endif

        <div class="rounded-lg bg-charcoal-50 dark:bg-charcoal-800 p-4 text-sm whitespace-pre-wrap">{{ $message->message }}</div>

        <div class="mt-6 flex gap-3">
            <a href="mailto:{{ $message->email }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold">Reply via Email</a>
            @if ($message->status !== 'replied')
                <form method="POST" action="{{ route('admin.contact-messages.mark-replied', $message) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg border border-charcoal-200 dark:border-charcoal-700 text-sm font-semibold">Mark as Replied</button>
                </form>
            @endif
            <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" onsubmit="return confirm('Delete this message?')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg border border-red-200 text-red-600 text-sm font-semibold">Delete</button>
            </form>
        </div>
    </div>
</x-admin-layout>
