<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('ecozeen-dark') === 'true', sidebarOpen: false }" x-init="$watch('dark', v => localStorage.setItem('ecozeen-dark', v))" :class="{ 'dark': dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#2563EB">
        <title>{{ config('app.name', 'Ecozeen Swap') }} Admin — {{ $title ?? 'Dashboard' }}</title>

        <link rel="icon" href="{{ \App\Models\SystemSetting::assetUrl('site_favicon', 'favicon.ico') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-charcoal-50 dark:bg-charcoal-950 text-charcoal-900 dark:text-charcoal-100">
        <div class="min-h-screen flex">
            <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 border-r border-charcoal-100 dark:border-charcoal-800 bg-charcoal-900">
                <div class="h-16 flex items-center px-6 border-b border-charcoal-800">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-white">
                        <span class="h-8 w-8 rounded-lg bg-brand-500 flex items-center justify-center font-bold">E</span>
                        <span class="font-bold">Admin Panel</span>
                    </a>
                </div>
                <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-charcoal-300">
                    @include('partials.admin-nav-items')
                </nav>
                <div class="p-3 border-t border-charcoal-800 space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-300 hover:bg-charcoal-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Exit to App
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-300 hover:bg-red-900/30 hover:text-red-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </aside>

            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" style="display:none"></div>
            <aside x-show="sidebarOpen" x-transition class="fixed inset-y-0 left-0 z-50 w-72 bg-charcoal-900 lg:hidden" style="display:none">
                <div class="h-16 flex items-center justify-between px-6 border-b border-charcoal-800 text-white">
                    <span class="font-bold">Admin Panel</span>
                    <button @click="sidebarOpen = false" class="text-charcoal-400"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>
                <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-charcoal-300" @click="sidebarOpen = false">
                    @include('partials.admin-nav-items')
                </nav>
            </aside>

            <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">
                <header class="sticky top-0 z-30 bg-white/80 dark:bg-charcoal-900/80 backdrop-blur border-b border-charcoal-100 dark:border-charcoal-800">
                    <div class="flex items-center justify-between px-4 sm:px-6 h-16">
                        <div class="flex items-center gap-3">
                            <button @click="sidebarOpen = true" class="lg:hidden text-charcoal-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            </button>
                            <h1 class="text-lg font-bold text-charcoal-900 dark:text-white">{{ $title ?? 'Dashboard' }}</h1>
                        </div>
                        <button type="button" @click="dark = !dark" class="rounded-full p-2 text-charcoal-500 hover:bg-charcoal-100 dark:hover:bg-charcoal-800">
                            <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" /></svg>
                            <svg x-show="dark" style="display:none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 111.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/></svg>
                        </button>
                    </div>
                </header>

                <main class="flex-1 px-4 sm:px-6 py-6 max-w-7xl w-full mx-auto">
                    @include('partials.flash-toast')

                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
