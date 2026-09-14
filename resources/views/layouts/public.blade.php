<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('ecozeen-dark') === 'true', mobileNavOpen: false }" :class="{ 'dark': dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#2563EB">
        <title>{{ $title ?? config('app.name') }} — {{ config('app.name') }}</title>

        <x-seo-meta :title="($title ?? config('app.name')).' — '.config('app.name')" :description="$description ?? null" :image="$image ?? null" />

        <link rel="manifest" href="{{ route('pwa.manifest') }}">
        <link rel="icon" href="{{ \App\Models\SystemSetting::assetUrl('site_favicon', 'favicon.ico') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        {{ $head ?? '' }}
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
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-semibold px-4 py-2 rounded-lg bg-brand-500 text-white hover:bg-brand-600">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-charcoal-600 dark:text-charcoal-300 hover:text-brand-600 px-3">Log in</a>
                            <a href="{{ route('register') }}" class="text-sm font-semibold px-4 py-2 rounded-lg bg-brand-500 text-white hover:bg-brand-600">Get Started</a>
                        @endauth
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
            {{ $slot }}
        </main>

        @include('partials.site-footer')
        @include('partials.support-widget')
        @livewireScripts
    </body>
</html>
