<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('ecozeen-dark') === 'true', mobileNavOpen: false }" :class="{ 'dark': dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#2563EB">
        <title>Ecozeen Swap — Buy, Sell &amp; Swap Crypto Securely</title>

        <x-seo-meta description="Ecozeen Swap — buy, sell, and swap crypto directly with Ecozeen Tech Ltd, your single trusted vendor. No P2P, no third-party ads." />

        <link rel="manifest" href="{{ route('pwa.manifest') }}">
        <link rel="icon" href="{{ \App\Models\SystemSetting::assetUrl('site_favicon', 'favicon.ico') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-white dark:bg-charcoal-950 text-charcoal-900 dark:text-white">
        <header class="sticky top-0 z-40 border-b border-charcoal-100 dark:border-charcoal-800 bg-white/80 dark:bg-charcoal-950/80 backdrop-blur">
            <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
                <a href="{{ route('home') }}"><x-application-logo class="h-9 w-9" /></a>
                @include('partials.public-nav-links')
                <div class="flex items-center gap-2">
                    <button type="button" @click="dark = !dark" class="rounded-full p-2 text-charcoal-500 hover:bg-charcoal-100 dark:hover:bg-charcoal-800">
                        <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" /></svg>
                        <svg x-show="dark" style="display:none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 111.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/></svg>
                    </button>
                    <div class="hidden md:flex items-center gap-2">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-charcoal-600 dark:text-charcoal-300 hover:text-brand-600 px-3">Log in</a>
                        <a href="{{ route('register') }}" class="text-sm font-semibold px-4 py-2 rounded-lg bg-brand-500 text-white hover:bg-brand-600">Get Started</a>
                    </div>
                    <button type="button" @click="mobileNavOpen = !mobileNavOpen" class="md:hidden rounded-lg p-2 text-charcoal-500 hover:bg-charcoal-100 dark:hover:bg-charcoal-800" aria-label="Toggle menu">
                        <svg x-show="!mobileNavOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <svg x-show="mobileNavOpen" style="display:none" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <div x-show="mobileNavOpen" x-transition @click.outside="mobileNavOpen = false" class="md:hidden border-t border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-950 px-6 py-4 space-y-1" style="display:none">
                @include('partials.public-nav-links-mobile')
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-brand-50 via-white to-white dark:from-charcoal-900 dark:via-charcoal-950 dark:to-charcoal-950 -z-10"></div>
                <div class="max-w-6xl mx-auto px-6 py-16 sm:py-24 grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 dark:bg-charcoal-800 text-brand-700 dark:text-brand-400 text-xs font-semibold px-3 py-1 ring-1 ring-brand-100 dark:ring-charcoal-700">
                            Ecozeen Tech Ltd &middot; Single Trusted Vendor
                        </span>
                        <h1 class="mt-5 text-4xl sm:text-6xl font-extrabold tracking-tight text-charcoal-900 dark:text-white leading-[1.05]">
                            Buy, Sell &amp; Swap Crypto — <span class="bg-gradient-to-r from-brand-500 to-brand-700 bg-clip-text text-transparent">directly with us.</span>
                        </h1>
                        <p class="mt-5 text-lg text-charcoal-500 dark:text-charcoal-400 max-w-lg">
                            No peer-to-peer ads, no third-party sellers, no waiting on strangers. Ecozeen Swap is the counter-party
                            for every trade, with transparent daily rates and fast settlement.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('register') }}" class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-semibold shadow-lg shadow-brand-500/20">Create Free Account</a>
                            <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl border border-charcoal-200 dark:border-charcoal-700 font-semibold text-charcoal-700 dark:text-charcoal-200">Log In</a>
                        </div>
                        <dl class="mt-10 grid grid-cols-3 gap-6 max-w-md">
                            <div>
                                <dt class="text-2xl font-extrabold text-charcoal-900 dark:text-white">24h</dt>
                                <dd class="text-xs text-charcoal-400">Rate refresh cycle</dd>
                            </div>
                            <div>
                                <dt class="text-2xl font-extrabold text-charcoal-900 dark:text-white">0</dt>
                                <dd class="text-xs text-charcoal-400">P2P ads or listings</dd>
                            </div>
                            <div>
                                <dt class="text-2xl font-extrabold text-charcoal-900 dark:text-white">1</dt>
                                <dd class="text-xs text-charcoal-400">Trusted counter-party</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-3xl bg-charcoal-900 text-white p-6 sm:p-8 shadow-2xl ring-1 ring-white/10">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-sm text-charcoal-400">Today's Rate Board</p>
                            <span class="flex items-center gap-1.5 text-[11px] text-brand-400"><span class="h-1.5 w-1.5 rounded-full bg-brand-400 animate-pulse"></span>Live</span>
                        </div>
                        <div class="space-y-3">
                            @forelse ($activeRates ?? [] as $rate)
                                <div class="flex items-center justify-between rounded-xl bg-charcoal-800 px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $rate->cryptoAsset->logoUrl() }}" class="h-6 w-6 rounded-full" alt="">
                                        <span class="font-semibold text-sm">{{ $rate->cryptoAsset->symbol }}/{{ $rate->fiatCurrency->code }}</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold">{{ number_format($rate->sell_rate, 2) }}</p>
                                        <p class="text-[10px] text-charcoal-400">buy rate</p>
                                    </div>
                                </div>
                            @empty
                                @foreach ([['BTC','NGN'], ['ETH','NGN'], ['USDT','USD']] as [$symbol, $code])
                                    <div class="flex items-center justify-between rounded-xl bg-charcoal-800 px-4 py-3">
                                        <span class="font-semibold text-sm">{{ $symbol }}/{{ $code }}</span>
                                        <span class="text-brand-400 text-xs">Coming online</span>
                                    </div>
                                @endforeach
                            @endforelse
                        </div>
                        <p class="mt-4 text-xs text-charcoal-500">Sign in to see all active rates with a live 24-hour countdown.</p>
                    </div>
                </div>
            </section>

            <section class="bg-charcoal-50 dark:bg-charcoal-900/50 py-16">
                <div class="max-w-6xl mx-auto px-6">
                    <h2 class="text-2xl sm:text-3xl font-bold text-center text-charcoal-900 dark:text-white">Everything you need, one vendor you can trust</h2>
                    <p class="text-center text-charcoal-500 dark:text-charcoal-400 mt-2 max-w-xl mx-auto">A complete crypto toolkit — trading, gift cards, and invoicing — all settled directly with Ecozeen Swap.</p>
                    <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ([
                            ['M12 4v16m0 0l-5-5m5 5l5-5', 'Buy Crypto', 'Fund via card, bank transfer, or Paystack/Flutterwave at our published sell rate.'],
                            ['M12 20V4m0 0l5 5m-5-5l-5 5', 'Sell Crypto', 'Send crypto to your dedicated address and get paid out in your local currency.'],
                            ['M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4', 'Swap Instantly', 'Convert between assets with a 5-minute rate lock — no manual approvals.'],
                            ['M12 8v13m0-13V6a2 2 0 10-2 2h2zm0 0a2 2 0 102-2h-2zm-8 5h16M5 8h14a1 1 0 011 1v3H4V9a1 1 0 011-1zm-1 4h16v7a1 1 0 01-1 1H5a1 1 0 01-1-1v-7z', 'Gift Cards & Invoices', 'Cash out gift cards or bill clients in crypto with a shareable invoice link.'],
                        ] as [$icon, $title, $desc])
                            <div class="rounded-2xl bg-white dark:bg-charcoal-900 border border-charcoal-100 dark:border-charcoal-800 p-6 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                                <div class="h-10 w-10 rounded-lg bg-brand-50 dark:bg-charcoal-800 text-brand-500 flex items-center justify-center mb-3">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                                </div>
                                <h3 class="font-semibold text-charcoal-900 dark:text-white">{{ $title }}</h3>
                                <p class="mt-2 text-sm text-charcoal-500 dark:text-charcoal-400">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="py-16">
                <div class="max-w-6xl mx-auto px-6">
                    <h2 class="text-2xl sm:text-3xl font-bold text-center text-charcoal-900 dark:text-white">How it works</h2>
                    <div class="mt-10 grid sm:grid-cols-3 gap-8">
                        @foreach ([
                            ['1', 'Create your account', 'Sign up with a username and email, then verify your identity to unlock higher trading limits.'],
                            ['2', 'Fund your wallet', 'Deposit fiat via Paystack, Flutterwave, or bank transfer — or send crypto to your dedicated address.'],
                            ['3', 'Trade instantly', 'Buy, sell, or swap at our published rates. Every trade settles directly with Ecozeen Swap.'],
                        ] as [$step, $title, $desc])
                            <div class="text-center">
                                <div class="h-12 w-12 rounded-full bg-brand-500 text-white font-bold text-lg flex items-center justify-center mx-auto mb-4">{{ $step }}</div>
                                <h3 class="font-semibold text-charcoal-900 dark:text-white">{{ $title }}</h3>
                                <p class="mt-2 text-sm text-charcoal-500 dark:text-charcoal-400">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="py-16 bg-brand-500">
                <div class="max-w-4xl mx-auto px-6 text-center text-white">
                    <h2 class="text-2xl sm:text-3xl font-bold">Ready to start trading?</h2>
                    <p class="mt-2 text-brand-50">Join Ecozeen Swap today and trade crypto with a vendor you can trust.</p>
                    <a href="{{ route('register') }}" class="mt-6 inline-flex items-center px-6 py-3 rounded-xl bg-white text-brand-700 font-semibold shadow-lg">Create Free Account</a>
                </div>
            </section>
        </main>

        @include('partials.site-footer')
        @livewireScripts
    </body>
</html>
