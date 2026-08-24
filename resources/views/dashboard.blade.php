<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <livewire:dashboard-stats />

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
                    <a href="{{ route('swap.index') }}" class="flex flex-col items-center gap-2 rounded-xl border border-charcoal-100 dark:border-charcoal-800 px-3 py-4 hover:border-brand-300 hover:bg-brand-50 dark:hover:bg-charcoal-800">
                        <svg class="h-6 w-6 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" /></svg>
                        <span class="text-xs font-semibold">Swap</span>
                    </a>
                    <a href="{{ route('wallet.fund') }}" class="flex flex-col items-center gap-2 rounded-xl border border-charcoal-100 dark:border-charcoal-800 px-3 py-4 hover:border-brand-300 hover:bg-brand-50 dark:hover:bg-charcoal-800">
                        <svg class="h-6 w-6 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        <span class="text-xs font-semibold">Fund Wallet</span>
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-5 shadow-sm">
                <h3 class="font-semibold text-charcoal-900 dark:text-white mb-4">Your Wallets</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ($wallets as $wallet)
                        <div class="rounded-xl bg-charcoal-50 dark:bg-charcoal-800 px-4 py-3">
                            <p class="text-xs text-charcoal-400">{{ $wallet->currency_code }}</p>
                            <p class="font-bold text-charcoal-900 dark:text-white">{{ number_format($wallet->balance, $wallet->isCrypto() ? 6 : 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div>
            <livewire:rate-timer-widget />
        </div>
    </div>
</x-app-layout>
