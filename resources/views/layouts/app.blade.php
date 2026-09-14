<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('ecozeen-dark') === 'true', sidebarOpen: false }" x-init="$watch('dark', v => localStorage.setItem('ecozeen-dark', v))" :class="{ 'dark': dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#2563EB">

        <title>{{ config('app.name', 'Ecozeen Swap') }} — {{ $title ?? 'Dashboard' }}</title>

        <link rel="manifest" href="{{ route('pwa.manifest') }}">
        <link rel="icon" href="{{ \App\Models\SystemSetting::assetUrl('site_favicon', 'favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ \App\Models\SystemSetting::assetUrl('pwa_icon_192', 'images/icons/icon-192.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-charcoal-50 dark:bg-charcoal-950 text-charcoal-900 dark:text-charcoal-100">
        <div class="min-h-screen flex">
            <!-- Desktop Sidebar -->
            <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 border-r border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900">
                <div class="h-16 flex items-center px-6 border-b border-charcoal-100 dark:border-charcoal-800">
                    <a href="{{ route('dashboard') }}"><x-application-logo class="h-9 w-9" /></a>
                </div>
                <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                    @include('partials.nav-items')
                </nav>
                <div class="p-3 border-t border-charcoal-100 dark:border-charcoal-800">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-600 dark:text-charcoal-300 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Mobile Sidebar Drawer -->
            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" style="display:none"></div>
            <aside x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-charcoal-900 shadow-xl lg:hidden" style="display:none">
                <div class="h-16 flex items-center justify-between px-6 border-b border-charcoal-100 dark:border-charcoal-800">
                    <x-application-logo class="h-9 w-9" />
                    <button @click="sidebarOpen = false" class="text-charcoal-400"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>
                <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1" @click="sidebarOpen = false">
                    @include('partials.nav-items')
                </nav>
                <div class="p-3 border-t border-charcoal-100 dark:border-charcoal-800">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-600 dark:text-charcoal-300 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main content -->
            <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">
                <!-- Top bar -->
                <header class="sticky top-0 z-30 bg-white/80 dark:bg-charcoal-900/80 backdrop-blur border-b border-charcoal-100 dark:border-charcoal-800">
                    <div class="flex items-center justify-between px-4 sm:px-6 h-16">
                        <div class="flex items-center gap-3">
                            <button @click="sidebarOpen = true" class="lg:hidden text-charcoal-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            </button>
                            <h1 class="text-lg font-bold text-charcoal-900 dark:text-white">{{ $title ?? 'Dashboard' }}</h1>
                        </div>

                        <div class="flex items-center gap-2">
                            <livewire:notification-bell />

                            <button type="button" @click="dark = !dark" class="rounded-full p-2 text-charcoal-500 hover:bg-charcoal-100 dark:hover:bg-charcoal-800" aria-label="Toggle dark mode">
                                <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" /></svg>
                                <svg x-show="dark" style="display:none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 111.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/></svg>
                            </button>

                            <x-dropdown align="right" width="56">
                                <x-slot name="trigger">
                                    <button class="flex items-center gap-2 rounded-full pl-1 pr-3 py-1 hover:bg-charcoal-100 dark:hover:bg-charcoal-800">
                                        @if (auth()->user()->avatarUrl())
                                            <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="h-8 w-8 rounded-full object-cover">
                                        @else
                                            <span class="h-8 w-8 rounded-full bg-brand-500 text-white flex items-center justify-center text-sm font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                        @endif
                                        <span class="hidden sm:block text-sm font-medium">{{ auth()->user()->username }}</span>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                                    <x-dropdown-link :href="route('security.two-factor')">Security &amp; 2FA</x-dropdown-link>
                                    <x-dropdown-link :href="route('security.kyc')">KYC Verification</x-dropdown-link>
                                    @if (auth()->user()->hasAnyRole(['admin', 'super-admin']))
                                        <x-dropdown-link :href="route('admin.dashboard')">Admin Panel</x-dropdown-link>
                                    @endif
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </header>

                @if (auth()->user()->kyc_status !== 'verified')
                    <div class="bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800 px-4 sm:px-6 py-2.5 text-sm text-amber-800 dark:text-amber-300 flex items-center gap-2">
                        <svg class="h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span>Complete KYC to increase your limits. You can keep using all features (buy, sell, swap, gift cards, deposits, withdrawals) in the meantime. <a href="{{ route('security.kyc') }}" class="font-semibold underline">Upload documents</a></span>
                    </div>
                @endif

                <main class="flex-1 px-4 sm:px-6 py-6 pb-24 lg:pb-6 max-w-6xl w-full mx-auto">
                    @include('partials.flash-toast')

                    {{ $slot }}
                </main>

                <footer class="hidden lg:block text-center text-xs text-charcoal-400 py-4">
                    &copy; {{ now()->year }} Ecozeen Tech Ltd &middot; NG: 1835204 &middot; UK: 16582062
                </footer>
            </div>
        </div>

        <!-- Mobile Bottom Navigation -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white dark:bg-charcoal-900 border-t border-charcoal-100 dark:border-charcoal-800 flex" style="padding-bottom: env(safe-area-inset-bottom)">
            <x-bottom-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10M9 21h6" /></svg></x-slot:icon>
                Home
            </x-bottom-nav-link>
            <x-bottom-nav-link :href="route('wallet.index')" :active="request()->routeIs('wallet.*')">
                <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2zm11 8h2" /></svg></x-slot:icon>
                Wallet
            </x-bottom-nav-link>
            <x-bottom-nav-link :href="route('buy.index')" :active="request()->routeIs('buy.*', 'sell.*', 'swap.*')">
                <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" /></svg></x-slot:icon>
                Trade
            </x-bottom-nav-link>
            <x-bottom-nav-link :href="route('giftcards.index')" :active="request()->routeIs('giftcards.*')">
                <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 10-2 2h2zm0 0a2 2 0 102-2h-2zm-8 5h16M5 8h14a1 1 0 011 1v3H4V9a1 1 0 011-1zm-1 4h16v7a1 1 0 01-1 1H5a1 1 0 01-1-1v-7z" /></svg></x-slot:icon>
                Cards
            </x-bottom-nav-link>
            <x-bottom-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*', 'security.*')">
                <x-slot:icon><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></x-slot:icon>
                More
            </x-bottom-nav-link>
        </nav>

        @include('partials.install-banner')
        @include('partials.support-widget')

        @livewireScripts
        <script src="{{ asset('js/pwa.js') }}" defer></script>
    </body>
</html>
