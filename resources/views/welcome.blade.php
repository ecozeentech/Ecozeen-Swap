<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('ecozeen-dark') === 'true' }" :class="{ 'dark': dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Ecozeen Swap — buy, sell, and swap crypto directly with Ecozeen Tech Ltd, your single trusted vendor. No P2P, no third-party ads.">
        <meta name="theme-color" content="#2D6A4F">
        <title>Ecozeen Swap — Buy, Sell &amp; Swap Crypto Securely</title>

        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <link rel="icon" href="{{ asset('images/icons/icon-192.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white dark:bg-charcoal-950 text-charcoal-900 dark:text-white">
        <header class="border-b border-charcoal-100 dark:border-charcoal-800">
            <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
                <x-application-logo :with-text="true" />
                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm font-semibold px-4 py-2 rounded-lg bg-brand-500 text-white hover:bg-brand-600">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-charcoal-600 dark:text-charcoal-300 hover:text-brand-600">Log in</a>
                        <a href="{{ route('register') }}" class="text-sm font-semibold px-4 py-2 rounded-lg bg-brand-500 text-white hover:bg-brand-600">Get Started</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            <section class="max-w-6xl mx-auto px-6 py-16 sm:py-24 grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 dark:bg-charcoal-800 text-brand-700 dark:text-brand-400 text-xs font-semibold px-3 py-1">
                        Ecozeen Tech Ltd &middot; Single Trusted Vendor
                    </span>
                    <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold tracking-tight text-charcoal-900 dark:text-white">
                        Buy, Sell &amp; Swap Crypto — <span class="text-brand-500">directly with us.</span>
                    </h1>
                    <p class="mt-5 text-lg text-charcoal-500 dark:text-charcoal-400">
                        No peer-to-peer ads, no third-party sellers, no waiting on strangers. Ecozeen Swap is the counter-party
                        for every trade, with transparent daily rates and fast settlement.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-semibold shadow-sm">Create Free Account</a>
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

                <div class="rounded-3xl bg-charcoal-900 text-white p-8 shadow-2xl">
                    <p class="text-sm text-charcoal-400">Today's Rate Board</p>
                    <div class="mt-4 space-y-3">
                        @foreach ([['BTC','NGN'], ['ETH','NGN'], ['USDT','USD']] as [$symbol, $code])
                            <div class="flex items-center justify-between rounded-xl bg-charcoal-800 px-4 py-3">
                                <span class="font-semibold">{{ $symbol }}/{{ $code }}</span>
                                <span class="text-brand-400 text-sm">Live in-app</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 text-xs text-charcoal-500">Sign in to see real-time buy/sell rates with a live 24-hour countdown.</p>
                </div>
            </section>

            <section class="bg-charcoal-50 dark:bg-charcoal-900/50 py-16">
                <div class="max-w-6xl mx-auto px-6">
                    <h2 class="text-2xl font-bold text-center text-charcoal-900 dark:text-white">Everything you need, one vendor you can trust</h2>
                    <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ([
                            ['Buy Crypto', 'Fund via card, bank transfer, or Paystack/Flutterwave at our published sell rate.'],
                            ['Sell Crypto', 'Send crypto to your dedicated address and get paid out in your local currency.'],
                            ['Swap Instantly', 'Convert between assets with a 5-minute rate lock — no manual approvals.'],
                            ['Gift Cards & Invoices', 'Cash out gift cards or bill clients in crypto with a shareable invoice link.'],
                        ] as [$title, $desc])
                            <div class="rounded-2xl bg-white dark:bg-charcoal-900 border border-charcoal-100 dark:border-charcoal-800 p-6 shadow-sm">
                                <h3 class="font-semibold text-charcoal-900 dark:text-white">{{ $title }}</h3>
                                <p class="mt-2 text-sm text-charcoal-500 dark:text-charcoal-400">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-charcoal-100 dark:border-charcoal-800 py-8 text-center text-xs text-charcoal-400">
            &copy; {{ now()->year }} Ecozeen Tech Ltd. Registered in Nigeria (RC 1835204) and the United Kingdom (16582062). All rights reserved.
        </footer>
    </body>
</html>
