<x-app-layout>
    <x-slot name="title">Swap Crypto</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex gap-2 justify-center">
            <a href="{{ route('buy.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-charcoal-600 dark:text-charcoal-300 border border-charcoal-200 dark:border-charcoal-700">Buy</a>
            <a href="{{ route('sell.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-charcoal-600 dark:text-charcoal-300 border border-charcoal-200 dark:border-charcoal-700">Sell</a>
            <a href="{{ route('swap.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold bg-brand-500 text-white">Swap</a>
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <livewire:swap-calculator />
        </div>

        <div>
            <h3 class="text-sm font-semibold text-charcoal-500 dark:text-charcoal-400 mb-3 uppercase tracking-wide">Your Crypto Balances</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach ($wallets as $wallet)
                    @php $asset = $cryptoAssets->firstWhere('symbol', $wallet->currency_code); @endphp
                    <div class="rounded-xl bg-charcoal-50 dark:bg-charcoal-800 px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if ($asset)
                                <img src="{{ $asset->logoUrl() }}" class="h-5 w-5 rounded-full object-cover" alt="">
                            @endif
                            <p class="text-xs text-charcoal-400">{{ $wallet->currency_code }}</p>
                        </div>
                        <p class="font-bold text-charcoal-900 dark:text-white mt-1">{{ number_format($wallet->balance, 6) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
