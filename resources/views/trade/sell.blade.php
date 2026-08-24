<x-app-layout>
    <x-slot name="title">Sell Crypto</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex gap-2 justify-center">
            <a href="{{ route('buy.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-charcoal-600 dark:text-charcoal-300 border border-charcoal-200 dark:border-charcoal-700">Buy</a>
            <a href="{{ route('sell.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold bg-brand-500 text-white">Sell</a>
            <a href="{{ route('swap.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-charcoal-600 dark:text-charcoal-300 border border-charcoal-200 dark:border-charcoal-700">Swap</a>
        </div>

        @if (auth()->user()->kyc_status !== 'verified')
            <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3 text-sm text-amber-800 dark:text-amber-300">
                KYC pending &mdash; trading limits apply. Your daily limit is <strong>${{ number_format(auth()->user()->daily_trade_limit, 2) }}</strong> USD equivalent.
            </div>
        @endif

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <form method="POST" action="{{ route('sell.store') }}" class="space-y-4">
                @csrf
                <livewire:sell-calculator />
                <x-primary-button class="w-full justify-center py-3">Continue</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
