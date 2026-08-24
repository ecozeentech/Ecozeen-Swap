<x-app-layout>
    <x-slot name="title">Bank Transfer Details</x-slot>

    <div class="max-w-lg mx-auto rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-charcoal-900 dark:text-white mb-2">Complete Your Bank Transfer</h2>
        <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-4">
            Transfer <span class="font-semibold text-charcoal-900 dark:text-white">{{ number_format($transaction->amount, 2) }} {{ $transaction->currency_code }}</span> to the account below, then upload your proof of payment.
        </p>

        <div class="rounded-xl bg-charcoal-50 dark:bg-charcoal-800 p-4 space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-charcoal-400">Bank Name</span><span class="font-semibold">{{ $bankDetails['bank_name'] ?? 'N/A' }}</span></div>
            <div class="flex justify-between"><span class="text-charcoal-400">Account Number</span><span class="font-semibold">{{ $bankDetails['account_number'] ?? 'N/A' }}</span></div>
            <div class="flex justify-between"><span class="text-charcoal-400">Account Name</span><span class="font-semibold">{{ $bankDetails['account_name'] ?? 'N/A' }}</span></div>
            <div class="flex justify-between"><span class="text-charcoal-400">Reference</span><span class="font-mono font-semibold">{{ $transaction->reference }}</span></div>
        </div>

        <div class="mt-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300 flex gap-2">
            <svg class="h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span><strong>Important:</strong> {{ \App\Models\SystemSetting::get('bank_transfer_memo_notice', 'Do not reference cryptocurrency or crypto payments in your bank transfer memo/description.') }}</span>
        </div>

        <form method="POST" action="{{ route('wallet.fund.proof', $transaction) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf
            <div>
                <x-input-label for="proof" value="Upload Payment Proof (screenshot)" />
                <input id="proof" name="proof" type="file" accept="image/*" required class="mt-1 w-full text-sm text-charcoal-600 dark:text-charcoal-300">
                <x-input-error :messages="$errors->get('proof')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="note" value="Note (optional)" />
                <textarea id="note" name="note" rows="2" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500"></textarea>
            </div>
            <x-primary-button class="w-full justify-center py-3">Submit Proof of Payment</x-primary-button>
        </form>
    </div>
</x-app-layout>
