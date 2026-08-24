<x-app-layout>
    <x-slot name="title">Support</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm text-center">
            <div class="h-14 w-14 mx-auto rounded-2xl bg-brand-50 dark:bg-charcoal-800 flex items-center justify-center text-brand-500 mb-4">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
            </div>
            <h2 class="text-lg font-bold text-charcoal-900 dark:text-white mb-2">Need Help?</h2>
            <p class="text-sm text-charcoal-500 dark:text-charcoal-400">Use the live chat widget in the corner of your screen, or reach us via the FAQs below.</p>
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 divide-y divide-charcoal-100 dark:divide-charcoal-800">
            @foreach ([
                ['Why can I trade before my KYC is fully verified?', 'Basic KYC does not block trading. You can trade immediately with lower daily limits until verification is complete.'],
                ['How do daily rates work?', 'Ecozeen Swap sets a buy/sell rate for each crypto/fiat pair that is valid for 24 hours, shown on your dashboard with a live countdown.'],
                ['Why should I avoid mentioning crypto in bank transfer memos?', 'Some banks restrict crypto-related transfers. Leaving the memo blank or generic helps avoid unnecessary delays.'],
                ['How long do sells take to confirm?', 'Once you send crypto to your provided address, our team confirms receipt and releases your fiat payout, typically within a short review window.'],
            ] as [$q, $a])
                <div class="px-5 py-4">
                    <p class="font-semibold text-sm text-charcoal-900 dark:text-white">{{ $q }}</p>
                    <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mt-1">{{ $a }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
