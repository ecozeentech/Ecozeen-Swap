<x-public-layout :title="$page->meta_title ?: $page->title" :description="$page->meta_description">
    <section class="max-w-4xl mx-auto px-6 py-16">
        <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 dark:bg-charcoal-800 text-brand-700 dark:text-brand-400 text-xs font-semibold px-3 py-1">
            About Ecozeen Swap
        </span>
        <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold text-charcoal-900 dark:text-white">{{ $page->title }}</h1>

        <div class="prose prose-charcoal dark:prose-invert max-w-none mt-8 text-charcoal-600 dark:text-charcoal-300 leading-relaxed [&_h2]:text-xl [&_h2]:font-bold [&_h2]:mt-8 [&_h2]:mb-3 [&_h2]:text-charcoal-900 [&_h2]:dark:text-white [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:mb-4">
            {!! $page->content !!}
        </div>

        <div class="mt-12 grid sm:grid-cols-3 gap-6 text-center">
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 p-6">
                <p class="text-2xl font-extrabold text-brand-600">RC 1835204</p>
                <p class="text-xs text-charcoal-400 mt-1">Registered in Nigeria</p>
            </div>
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 p-6">
                <p class="text-2xl font-extrabold text-brand-600">16582062</p>
                <p class="text-xs text-charcoal-400 mt-1">Registered in the United Kingdom</p>
            </div>
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 p-6">
                <p class="text-2xl font-extrabold text-brand-600">1</p>
                <p class="text-xs text-charcoal-400 mt-1">Trusted counter-party for every trade</p>
            </div>
        </div>
    </section>
</x-public-layout>
