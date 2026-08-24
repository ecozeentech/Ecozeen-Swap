<x-app-layout>
    <x-slot name="title">Buy Crypto</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex gap-2 justify-center">
            <a href="{{ route('buy.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold bg-brand-500 text-white">Buy</a>
            <a href="{{ route('sell.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-charcoal-600 dark:text-charcoal-300 border border-charcoal-200 dark:border-charcoal-700">Sell</a>
            <a href="{{ route('swap.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-charcoal-600 dark:text-charcoal-300 border border-charcoal-200 dark:border-charcoal-700">Swap</a>
        </div>

        @if (auth()->user()->kyc_status !== 'verified')
            <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3 text-sm text-amber-800 dark:text-amber-300">
                KYC pending &mdash; trading limits apply. Your daily limit is <strong>${{ number_format(auth()->user()->daily_trade_limit, 2) }}</strong> USD equivalent.
            </div>
        @endif

        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300 flex gap-2">
            <svg class="h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span><strong>Important notice:</strong> Do not reference cryptocurrency or crypto payments in your bank transfer memo/description.</span>
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <form method="POST" action="{{ route('buy.store') }}" class="space-y-4">
                @csrf
                <livewire:buy-calculator />

                <div>
                    <x-input-label value="Payment Method" />
                    <div class="mt-2 space-y-2">
                        @foreach ($gateways as $gateway)
                            <label class="flex items-center gap-3 rounded-lg border border-charcoal-200 dark:border-charcoal-700 px-4 py-3 cursor-pointer hover:border-brand-400">
                                <input type="radio" name="payment_method" value="{{ $gateway->slug }}" required class="text-brand-600 focus:ring-brand-500">
                                <span class="text-sm font-medium">{{ $gateway->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <x-primary-button class="w-full justify-center py-3">Continue to Payment</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
