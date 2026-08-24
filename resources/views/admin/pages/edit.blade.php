<x-admin-layout>
    <x-slot name="title">{{ $page->exists ? 'Edit Page' : 'New Page' }}</x-slot>

    <div class="max-w-3xl rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
        <form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="space-y-4">
            @csrf
            @if ($page->exists) @method('PUT') @endif

            <div>
                <x-input-label value="Title" />
                <x-text-input name="title" value="{{ old('title', $page->title) }}" required class="mt-1 w-full" />
            </div>

            <div>
                <x-input-label value="URL Slug" />
                <x-text-input name="slug" value="{{ old('slug', $page->slug) }}" :disabled="in_array($page->slug, ['about-us', 'contact-us', 'privacy-policy', 'terms-of-service'])" class="mt-1 w-full" />
                <p class="text-xs text-charcoal-400 mt-1">Page will be available at /{{ $page->slug ?: 'your-slug' }}</p>
            </div>

            <div>
                <x-input-label value="Content" />
                <textarea name="content" rows="16" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white font-mono text-sm">{{ old('content', $page->content) }}</textarea>
                <p class="text-xs text-charcoal-400 mt-1">Basic HTML is supported (headings, paragraphs, lists, links, images).</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label value="Meta Title (SEO)" />
                    <x-text-input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="Meta Description (SEO)" />
                    <x-text-input name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" class="mt-1 w-full" />
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published ?? true)) class="rounded border-charcoal-300 text-brand-600 focus:ring-brand-500">
                Published (visible on the public site)
            </label>

            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.pages.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-charcoal-200 dark:border-charcoal-700 text-sm font-semibold">Back</a>
                <x-primary-button class="px-6">Save Page</x-primary-button>
            </div>
        </form>
    </div>
</x-admin-layout>
