<x-public-layout :title="$post->metaTitle()" :description="$post->metaDescription()" :image="$post->featuredImageUrl()">
    <x-slot:head>
        <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'image' => [$post->featuredImageUrl()],
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => ['@type' => 'Organization', 'name' => 'Ecozeen Swap'],
            'publisher' => ['@type' => 'Organization', 'name' => 'Ecozeen Tech Ltd'],
            'description' => $post->metaDescription(),
        ]) !!}
        </script>
    </x-slot:head>

    <article class="max-w-3xl mx-auto px-6 py-16">
        <div class="text-center">
            @if ($post->category)
                <a href="{{ route('blog.category', $post->category->slug) }}" class="text-xs font-semibold text-brand-600">{{ $post->category->name }}</a>
            @endif
            <h1 class="mt-3 text-3xl sm:text-4xl font-extrabold text-charcoal-900 dark:text-white">{{ $post->title }}</h1>
            <p class="mt-3 text-sm text-charcoal-400">
                {{ $post->published_at?->format('F d, Y') }} &middot; {{ $post->readingTimeMinutes() }} min read &middot; {{ $post->views }} views
                @if ($post->author) &middot; by {{ $post->author->name }} @endif
            </p>
        </div>

        <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" class="mt-8 w-full h-64 sm:h-96 object-cover rounded-2xl">

        <div class="prose prose-charcoal dark:prose-invert max-w-none mt-10 text-charcoal-600 dark:text-charcoal-300 leading-relaxed [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:mt-8 [&_h2]:mb-3 [&_h2]:text-charcoal-900 [&_h2]:dark:text-white [&_h3]:text-lg [&_h3]:font-bold [&_h3]:mt-6 [&_h3]:mb-2 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:mb-4 [&_a]:text-brand-600 [&_a]:underline [&_img]:rounded-xl">
            {!! $post->content !!}
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="max-w-6xl mx-auto px-6 pb-16">
            <h2 class="text-xl font-bold text-charcoal-900 dark:text-white mb-6">Related Posts</h2>
            <div class="grid sm:grid-cols-3 gap-6">
                @foreach ($related as $item)
                    <a href="{{ route('blog.show', $item->slug) }}" class="group rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 overflow-hidden shadow-sm hover:shadow-lg transition">
                        <img src="{{ $item->featuredImageUrl() }}" class="h-32 w-full object-cover" alt="">
                        <div class="p-4">
                            <h3 class="font-semibold text-sm text-charcoal-900 dark:text-white group-hover:text-brand-600">{{ $item->title }}</h3>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</x-public-layout>
