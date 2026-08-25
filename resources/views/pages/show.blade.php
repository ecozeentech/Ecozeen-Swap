<x-public-layout :title="$page->meta_title ?: $page->title" :description="$page->meta_description">
    <section class="max-w-5xl mx-auto px-6 py-16">
        <div class="grid lg:grid-cols-4 gap-8">
            @if (count($tableOfContents) > 1)
                <aside class="hidden lg:block lg:col-span-1">
                    <div class="sticky top-24 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-400 mb-3">Policy Navigation</p>
                        <ul class="space-y-2 text-sm">
                            @foreach ($tableOfContents as $index => $item)
                                <li>
                                    <a href="#{{ $item['id'] }}" class="{{ $index === 1 ? 'text-charcoal-900 dark:text-white font-semibold' : 'text-charcoal-500 dark:text-charcoal-400' }} hover:text-brand-600">{{ $item['title'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </aside>
            @endif

            <div class="{{ count($tableOfContents) > 1 ? 'lg:col-span-3' : 'lg:col-span-4' }} rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 sm:p-10 shadow-sm">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-charcoal-900 dark:text-white">{{ $page->title }}</h1>
                <p class="text-sm text-charcoal-400 mt-2">Last updated {{ $page->updated_at->format('F d, Y') }}</p>

                <div class="prose dark:prose-invert max-w-none mt-8 text-charcoal-600 dark:text-charcoal-300 leading-relaxed [&_h2]:text-xl [&_h2]:font-bold [&_h2]:mt-8 [&_h2]:mb-3 [&_h2]:text-charcoal-900 [&_h2]:dark:text-white [&_h3]:text-base [&_h3]:font-bold [&_h3]:mt-6 [&_h3]:mb-2 [&_h3]:text-charcoal-900 [&_h3]:dark:text-white [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:mb-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:mb-4">
                    {!! $content !!}
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
