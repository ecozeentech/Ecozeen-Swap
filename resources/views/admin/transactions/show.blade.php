<x-admin-layout>
    <x-slot name="title">Transaction {{ $transaction->reference }}</x-slot>

    <div class="max-w-2xl mx-auto rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold">{{ $transaction->reference }}</h2>
            <span @class([
                'text-xs font-medium px-2 py-1 rounded-full capitalize',
                'bg-green-100 text-green-700' => $transaction->status === 'completed',
                'bg-amber-100 text-amber-700' => in_array($transaction->status, ['pending', 'processing']),
                'bg-red-100 text-red-700' => in_array($transaction->status, ['failed', 'cancelled']),
            ])>{{ $transaction->status }}</span>
        </div>

        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-charcoal-400">User</dt><dd class="font-semibold">{{ $transaction->user->username ?? 'N/A' }}</dd></div>
            <div class="flex justify-between"><dt class="text-charcoal-400">Type</dt><dd class="font-semibold capitalize">{{ $transaction->type }}</dd></div>
            <div class="flex justify-between"><dt class="text-charcoal-400">Amount</dt><dd class="font-semibold">{{ number_format($transaction->amount, 8) }} {{ $transaction->currency_code }}</dd></div>
            <div class="flex justify-between"><dt class="text-charcoal-400">Created</dt><dd class="font-semibold">{{ $transaction->created_at->format('M d, Y H:i') }}</dd></div>
        </dl>

        @if ($transaction->metadata)
            <div class="mt-4 rounded-lg bg-charcoal-50 dark:bg-charcoal-800 p-3 text-xs font-mono overflow-x-auto">
                <pre>{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

        @if (in_array($transaction->status, ['pending', 'processing']))
            <div class="mt-6 flex gap-3">
                @if (in_array($transaction->type, ['buy', 'deposit']))
                    <form method="POST" action="{{ route('admin.transactions.mark-paid', $transaction) }}">
                        @csrf
                        <x-primary-button>Mark as Paid</x-primary-button>
                    </form>
                @endif

                @if ($transaction->type === 'sell')
                    <form method="POST" action="{{ route('admin.transactions.confirm-sell', $transaction) }}">
                        @csrf
                        <x-primary-button>Confirm Crypto Received</x-primary-button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.transactions.reject', $transaction) }}" onsubmit="return promptReject(this)">
                    @csrf
                    <input type="hidden" name="reason" value="">
                    <x-danger-button>Reject</x-danger-button>
                </form>
            </div>
        @endif
    </div>

    <script>
        function promptReject(form) {
            const reason = prompt('Rejection reason:');
            if (!reason) return false;
            form.querySelector('input[name="reason"]').value = reason;
            return true;
        }
    </script>
</x-admin-layout>
