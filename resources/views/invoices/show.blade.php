<x-app-layout>
    <x-slot name="title">Invoice {{ $invoice->invoice_number }}</x-slot>

    <div class="max-w-lg mx-auto rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-charcoal-900 dark:text-white">Invoice {{ $invoice->invoice_number }}</h2>
            <span @class([
                'text-xs font-medium px-2 py-1 rounded-full capitalize',
                'bg-green-100 text-green-700' => $invoice->status === 'paid',
                'bg-amber-100 text-amber-700' => in_array($invoice->status, ['draft', 'sent']),
                'bg-red-100 text-red-700' => in_array($invoice->status, ['expired', 'cancelled']),
            ])>{{ $invoice->status }}</span>
        </div>

        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-charcoal-400">Amount</dt><dd class="font-semibold">{{ number_format($invoice->amount_crypto, 8) }} {{ $invoice->cryptoAsset->symbol }}</dd></div>
            <div class="flex justify-between"><dt class="text-charcoal-400">Recipient</dt><dd class="font-semibold">{{ $invoice->recipient_email }}</dd></div>
            @if ($invoice->recipient_name)
                <div class="flex justify-between"><dt class="text-charcoal-400">Name</dt><dd class="font-semibold">{{ $invoice->recipient_name }}</dd></div>
            @endif
            @if ($invoice->due_date)
                <div class="flex justify-between"><dt class="text-charcoal-400">Due Date</dt><dd class="font-semibold">{{ $invoice->due_date->format('M d, Y') }}</dd></div>
            @endif
        </dl>

        @if ($invoice->description)
            <div class="mt-4 rounded-lg bg-charcoal-50 dark:bg-charcoal-800 p-3 text-sm text-charcoal-600 dark:text-charcoal-300">
                {{ $invoice->description }}
            </div>
        @endif

        <div class="mt-6 rounded-lg bg-brand-50 dark:bg-charcoal-800 p-3 text-xs text-charcoal-500 dark:text-charcoal-400">
            Shareable link:
            <span class="font-mono break-all">{{ route('invoices.show', $invoice) }}</span>
        </div>

        <a href="{{ route('invoices.index') }}" class="mt-6 inline-flex items-center px-4 py-2 rounded-lg border border-charcoal-200 dark:border-charcoal-700 text-sm font-semibold">Back to Invoices</a>
    </div>
</x-app-layout>
