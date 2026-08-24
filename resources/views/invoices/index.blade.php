<x-app-layout>
    <x-slot name="title">Invoicing</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-charcoal-900 dark:text-white">Crypto Invoices</h2>
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold">New Invoice</a>
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-charcoal-400 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3">Invoice #</th>
                        <th class="px-5 py-3">Recipient</th>
                        <th class="px-5 py-3">Amount</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-charcoal-50 dark:hover:bg-charcoal-800 cursor-pointer" onclick="window.location='{{ route('invoices.show', $invoice) }}'">
                            <td class="px-5 py-3 font-mono text-xs">{{ $invoice->invoice_number }}</td>
                            <td class="px-5 py-3">{{ $invoice->recipient_email }}</td>
                            <td class="px-5 py-3">{{ number_format($invoice->amount_crypto, 6) }} {{ $invoice->cryptoAsset->symbol }}</td>
                            <td class="px-5 py-3 capitalize">{{ $invoice->status }}</td>
                            <td class="px-5 py-3 text-charcoal-400">{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-6 text-center text-charcoal-400">No invoices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $invoices->links() }}
    </div>
</x-app-layout>
