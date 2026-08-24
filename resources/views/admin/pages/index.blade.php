<x-admin-layout>
    <x-slot name="title">Pages</x-slot>

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-charcoal-500 dark:text-charcoal-400">Manage static content pages shown on the public site.</p>
        <a href="{{ route('admin.pages.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold">New Page</a>
    </div>

    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm divide-y divide-charcoal-100 dark:divide-charcoal-800">
        @foreach ($pages as $page)
            <div class="flex items-center justify-between px-5 py-4">
                <div>
                    <p class="font-semibold">{{ $page->title }}</p>
                    <p class="text-xs text-charcoal-400">/{{ $page->slug }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $page->is_published ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600' }}">{{ $page->is_published ? 'Published' : 'Draft' }}</span>
                    <a href="{{ route('admin.pages.edit', $page) }}" class="text-sm font-semibold text-brand-600 hover:underline">Edit</a>
                    @unless (in_array($page->slug, ['about-us', 'contact-us', 'privacy-policy', 'terms-of-service']))
                        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Delete this page?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-red-600 hover:underline">Delete</button>
                        </form>
                    @endunless
                </div>
            </div>
        @endforeach
    </div>
</x-admin-layout>
