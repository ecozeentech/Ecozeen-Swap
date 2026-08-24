<x-app-layout>
    <x-slot name="title">Complete Your Sale</x-slot>

    <div class="max-w-lg mx-auto rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm text-center">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-brand-50 dark:bg-charcoal-800 flex items-center justify-center text-brand-500 mb-4">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20V4m0 0l5 5m-5-5l-5 5" /></svg>
        </div>
        <h2 class="text-lg font-bold text-charcoal-900 dark:text-white mb-2">Send {{ $transaction->metadata['crypto_amount'] }} {{ $transaction->metadata['crypto_symbol'] }}</h2>
        <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-4">
            Send exactly the amount above to the address below. Once received, our team will confirm and credit
            <strong>{{ number_format($transaction->amount, 2) }} {{ $transaction->currency_code }}</strong> to your fiat wallet.
        </p>

        <div class="rounded-xl bg-charcoal-50 dark:bg-charcoal-800 p-4 break-all font-mono text-sm">
            {{ $transaction->metadata['deposit_address'] }}
        </div>

        <div class="mt-4 rounded-lg bg-charcoal-50 dark:bg-charcoal-800 p-3 text-sm flex justify-between">
            <span class="text-charcoal-400">Reference</span>
            <span class="font-mono font-semibold">{{ $transaction->reference }}</span>
        </div>

        <div class="mt-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 px-4 py-2 text-xs text-amber-700 dark:text-amber-300">
            Status: <span class="font-semibold capitalize">{{ $transaction->status }}</span> &mdash; awaiting confirmation
        </div>

        <a href="{{ route('wallet.index') }}" class="mt-6 inline-flex items-center px-5 py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold">Go to Wallet</a>
    </div>
</x-app-layout>
