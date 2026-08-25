<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="mb-6">
        <h1 class="text-xl font-bold text-charcoal-900 dark:text-white">Overview</h1>
        <p class="text-sm text-charcoal-500 dark:text-charcoal-400">Welcome back, {{ auth()->user()->name }}. Here's how your portfolio is doing.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <livewire:dashboard-stats />

            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-charcoal-900 dark:text-white">Your Assets</h3>
                    <a href="{{ route('wallet.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">View Wallet</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($wallets as $wallet)
                        @php
                            $logoModel = $wallet->isCrypto() ? $cryptoAssets->firstWhere('symbol', $wallet->currency_code) : $fiatCurrencies->firstWhere('code', $wallet->currency_code);
                            $name = $logoModel->name ?? $wallet->currency_code;
                        @endphp
                        <div class="flex items-center justify-between rounded-xl border border-charcoal-100 dark:border-charcoal-800 px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($logoModel)
                                    <img src="{{ $logoModel->logoUrl() }}" class="h-8 w-8 rounded-full object-cover" alt="">
                                @else
                                    <span class="h-8 w-8 rounded-full bg-charcoal-100 dark:bg-charcoal-800 flex items-center justify-center text-xs font-bold text-charcoal-500">{{ substr($wallet->currency_code, 0, 1) }}</span>
                                @endif
                                <div>
                                    <p class="text-sm font-semibold text-charcoal-900 dark:text-white">{{ $name }}</p>
                                    <p class="text-xs text-charcoal-400">{{ $wallet->currency_code }}</p>
                                </div>
                            </div>
                            <p class="font-bold text-charcoal-900 dark:text-white">{{ number_format($wallet->balance, $wallet->isCrypto() ? 4 : 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-5 shadow-sm">
                <h3 class="font-semibold text-charcoal-900 dark:text-white mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <a href="{{ route('buy.index') }}" class="flex flex-col items-center gap-2 rounded-xl border border-charcoal-100 dark:border-charcoal-800 px-3 py-4 hover:border-brand-300 hover:bg-brand-50 dark:hover:bg-charcoal-800">
                        <svg class="h-6 w-6 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0 0l-5-5m5 5l5-5" /></svg>
                        <span class="text-xs font-semibold">Buy</span>
                    </a>
                    <a href="{{ route('sell.index') }}" class="flex flex-col items-center gap-2 rounded-xl border border-charcoal-100 dark:border-charcoal-800 px-3 py-4 hover:border-brand-300 hover:bg-brand-50 dark:hover:bg-charcoal-800">
                        <svg class="h-6 w-6 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20V4m0 0l5 5m-5-5l-5 5" /></svg>
                        <span class="text-xs font-semibold">Sell</span>
                    </a>
                    <a href="{{ route('giftcards.index') }}" class="flex flex-col items-center gap-2 rounded-xl border border-charcoal-100 dark:border-charcoal-800 px-3 py-4 hover:border-brand-300 hover:bg-brand-50 dark:hover:bg-charcoal-800">
                        <svg class="h-6 w-6 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 10-2 2h2zm0 0a2 2 0 102-2h-2zm-8 5h16M5 8h14a1 1 0 011 1v3H4V9a1 1 0 011-1zm-1 4h16v7a1 1 0 01-1 1H5a1 1 0 01-1-1v-7z" /></svg>
                        <span class="text-xs font-semibold">Gift Cards</span>
                    </a>
                    <a href="{{ route('wallet.fund') }}" class="flex flex-col items-center gap-2 rounded-xl border border-charcoal-100 dark:border-charcoal-800 px-3 py-4 hover:border-brand-300 hover:bg-brand-50 dark:hover:bg-charcoal-800">
                        <svg class="h-6 w-6 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        <span class="text-xs font-semibold">Fund Wallet</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl bg-charcoal-900 text-white p-6 shadow-lg relative overflow-hidden">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-brand-500/20"></div>
                <div class="h-10 w-10 rounded-full bg-brand-500/20 text-brand-400 flex items-center justify-center mb-3">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
                <h3 class="font-bold">Zero Fee Trading</h3>
                <p class="mt-2 text-sm text-charcoal-300">Sell crypto directly to Ecozeen Swap with zero network fees on settlement.</p>
                <a href="{{ route('sell.index') }}" class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold">Sell Now</a>
            </div>

            <livewire:rate-timer-widget />
        </div>
    </div>
</x-app-layout>
