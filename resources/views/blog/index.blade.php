<x-public-layout title="Blog" description="Insights, product updates, and market notes from the Ecozeen Swap team.">
    <section class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 dark:bg-charcoal-800 text-brand-700 dark:text-brand-400 text-xs font-semibold px-3 py-1">Blog</span>
            <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold text-charcoal-900 dark:text-white">
                @isset($activeCategory) {{ $activeCategory->name }} @else Insights &amp; Updates @endisset
            </h1>
            <p class="mt-3 text-charcoal-500 dark:text-charcoal-400">News, market notes, and product updates from the Ecozeen Swap team.</p>
        </div>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-2">
            <a href="{{ route('blog.index') }}" class="px-4 py-1.5 rounded-full text-sm font-medium {{ ! isset($activeCategory) ? 'bg-brand-500 text-white' : 'bg-charcoal-100 dark:bg-charcoal-800 text-charcoal-600 dark:text-charcoal-300' }}">All</a>
            @foreach ($categories as $category)
                <a href="{{ route('blog.category', $category->slug) }}" class="px-4 py-1.5 rounded-full text-sm font-medium {{ (isset($activeCategory) && $activeCategory->id === $category->id) ? 'bg-brand-500 text-white' : 'bg-charcoal-100 dark:bg-charcoal-800 text-charcoal-600 dark:text-charcoal-300' }}">{{ $category->name }}</a>
            @endforeach
        </div>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="group rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 overflow-hidden shadow-sm hover:shadow-lg transition">
                    <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" class="h-44 w-full object-cover">
                    <div class="p-5">
                        @if ($post->category)
                            <span class="text-xs font-semibold text-brand-600">{{ $post->category->name }}</span>
                        @endif
                        <h2 class="font-bold text-charcoal-900 dark:text-white mt-1 group-hover:text-brand-600">{{ $post->title }}</h2>
                        <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mt-2">{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 100) }}</p>
                        <p class="text-xs text-charcoal-400 mt-3">{{ $post->published_at?->format('M d, Y') }} &middot; {{ $post->readingTimeMinutes() }} min read</p>
                    </div>
                </a>
            @empty
                <p class="col-span-full text-center text-charcoal-400 py-12">No posts published yet. Check back soon.</p>
            @endforelse
        </div>

        <div class="mt-10">{{ $posts->links() }}</div>
    </section>
</x-public-layout>
