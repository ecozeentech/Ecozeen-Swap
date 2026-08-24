<x-app-layout>
    <x-slot name="title">Deposit {{ $asset->symbol }}</x-slot>

    <div class="max-w-lg mx-auto rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm text-center">
        <h2 class="text-lg font-bold text-charcoal-900 dark:text-white mb-2">Deposit {{ $asset->name }} ({{ $asset->symbol }})</h2>
        <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-4">Send only {{ $asset->symbol }} ({{ $asset->network }} network) to the address below. Sending any other asset may result in permanent loss.</p>

        <div class="rounded-xl bg-charcoal-50 dark:bg-charcoal-800 p-4 break-all font-mono text-sm">
            {{ $address->address }}
        </div>

        @if ($address->memo_tag)
            <div class="mt-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 p-3 text-sm">
                <span class="text-charcoal-400">Memo/Tag (required):</span>
                <span class="font-mono font-semibold ml-1">{{ $address->memo_tag }}</span>
            </div>
        @endif

        <button type="button" onclick="navigator.clipboard.writeText('{{ $address->address }}')" class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold">
            Copy Address
        </button>

        <p class="mt-4 text-xs text-charcoal-400">Deposits are usually credited after network confirmation. Contact support if your deposit hasn't arrived after the expected confirmation time.</p>
    </div>
</x-app-layout>
