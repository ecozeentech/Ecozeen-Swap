<x-public-layout :title="$page->meta_title ?: $page->title" :description="$page->meta_description">
    <section class="max-w-5xl mx-auto px-6 py-16">
        <div class="text-center max-w-2xl mx-auto">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-charcoal-900 dark:text-white leading-tight">
                Bridging the Gap to <span class="text-brand-600">Financial Inclusion</span>
            </h1>
            <p class="mt-4 text-charcoal-500 dark:text-charcoal-400">
                Ecozeen Tech Ltd is a technology company bridging the gap between traditional finance and
                decentralized ecosystems across NG &amp; UK.
            </p>
        </div>

        <div class="mt-12 grid lg:grid-cols-2 gap-6 items-stretch">
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                <h2 class="font-bold text-charcoal-900 dark:text-white flex items-center gap-2">
                    <svg class="h-5 w-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    Our Story
                </h2>
                <div class="prose dark:prose-invert max-w-none mt-4 text-sm text-charcoal-600 dark:text-charcoal-300 leading-relaxed [&_p]:mb-4">
                    {!! $page->content !!}
                </div>
            </div>

            <div class="relative rounded-2xl overflow-hidden shadow-sm min-h-[260px] bg-gradient-to-br from-brand-600 to-charcoal-900 flex items-end p-6">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(255,255,255,0.15),transparent_60%)]"></div>
                <span class="relative text-white text-xs font-semibold tracking-widest uppercase bg-black/20 px-3 py-1.5 rounded-full">Global Reach</span>
            </div>
        </div>

        <div class="mt-16 text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-charcoal-900 dark:text-white">The Ecozeen Advantage</h2>
            <p class="mt-2 text-charcoal-500 dark:text-charcoal-400">Engineered for precision. Built for trust.</p>
        </div>

        <div class="mt-8 grid sm:grid-cols-3 gap-6">
            @foreach ([
                ['M13 10V3L4 14h7v7l9-11h-7z', 'Frictionless Speed', 'Institutional-grade execution engines ensure your trades settle instantly, minimizing slippage even in volatile markets.'],
                ['M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'Uncompromising Security', 'Encrypted data at rest, real-time activity monitoring, and strict verification protocols form the bedrock of our trust architecture.'],
                ['M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'Dedicated Support', 'Our specialized support team provides rapid, precise assistance, ensuring your operations never experience downtime.'],
            ] as [$icon, $title, $desc])
                <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm text-left">
                    <div class="h-10 w-10 rounded-full bg-brand-50 dark:bg-charcoal-800 text-brand-500 flex items-center justify-center mb-3">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                    </div>
                    <h3 class="font-semibold text-charcoal-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-charcoal-500 dark:text-charcoal-400">{{ $desc }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-16">
            <h2 class="text-2xl sm:text-3xl font-bold text-charcoal-900 dark:text-white">Leadership Team</h2>
            <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-6">
                @foreach ([
                    ['Executive Name', 'Chief Executive Officer'],
                    ['Executive Name', 'Chief Technology Officer'],
                ] as [$name, $title])
                    <div>
                        <div class="aspect-square rounded-2xl bg-charcoal-100 dark:bg-charcoal-800 flex items-center justify-center overflow-hidden">
                            <svg class="h-16 w-16 text-charcoal-300 dark:text-charcoal-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.418 0-8 2.239-8 5v1a1 1 0 001 1h14a1 1 0 001-1v-1c0-2.761-3.582-5-8-5z" /></svg>
                        </div>
                        <p class="mt-3 font-semibold text-sm text-charcoal-900 dark:text-white">{{ $name }}</p>
                        <p class="text-xs text-charcoal-400">{{ $title }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-16 grid sm:grid-cols-3 gap-6 text-center">
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
