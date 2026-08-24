<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('ecozeen-dark') === 'true' }" x-init="$watch('dark', v => localStorage.setItem('ecozeen-dark', v))" :class="{ 'dark': dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#2D6A4F">
        <meta name="description" content="Ecozeen Swap — buy, sell, and swap crypto directly with Ecozeen Tech Ltd, your single trusted vendor.">

        <title>{{ config('app.name', 'Ecozeen Swap') }} — {{ $title ?? 'Secure Crypto Trading' }}</title>

        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <link rel="icon" href="{{ \App\Models\SystemSetting::assetUrl('site_favicon', 'images/icons/icon-192.png') }}">
        <link rel="apple-touch-icon" href="{{ \App\Models\SystemSetting::assetUrl('site_favicon', 'images/icons/icon-192.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-charcoal-900 antialiased bg-charcoal-50 dark:bg-charcoal-950 dark:text-charcoal-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="w-full sm:max-w-md px-4 flex items-center justify-between">
                <a href="/" class="inline-block">
                    <x-application-logo :with-text="true" />
                </a>
                <button type="button" @click="dark = !dark" class="rounded-full p-2 text-charcoal-500 hover:bg-charcoal-100 dark:hover:bg-charcoal-800" aria-label="Toggle dark mode">
                    <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" /></svg>
                    <svg x-show="dark" style="display:none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 111.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/></svg>
                </button>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white dark:bg-charcoal-900 shadow-md overflow-hidden sm:rounded-2xl border border-charcoal-100 dark:border-charcoal-800">
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-charcoal-400 text-center px-4">
                &copy; {{ now()->year }} Ecozeen Tech Ltd. NG: 1835204 &middot; UK: 16582062. All rights reserved.
            </p>
        </div>
    </body>
</html>
