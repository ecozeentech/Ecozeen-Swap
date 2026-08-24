<x-public-layout :title="$page->meta_title ?: $page->title" :description="$page->meta_description">
    <section class="max-w-3xl mx-auto px-6 py-16">
        <h1 class="text-3xl sm:text-4xl font-extrabold text-charcoal-900 dark:text-white">{{ $page->title }}</h1>
        <p class="text-sm text-charcoal-400 mt-2">Last updated {{ $page->updated_at->format('F d, Y') }}</p>

        <div class="prose prose-charcoal dark:prose-invert max-w-none mt-8 text-charcoal-600 dark:text-charcoal-300 leading-relaxed [&_h2]:text-xl [&_h2]:font-bold [&_h2]:mt-8 [&_h2]:mb-3 [&_h2]:text-charcoal-900 [&_h2]:dark:text-white [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:mb-4">
            {!! $page->content !!}
        </div>
    </section>
</x-public-layout>
