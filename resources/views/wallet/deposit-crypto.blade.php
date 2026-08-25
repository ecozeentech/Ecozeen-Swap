<x-app-layout>
    <x-slot name="title">Deposit {{ $asset->symbol }}</x-slot>

    <div class="max-w-lg mx-auto rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm text-center" x-data="{ selected: {{ $platformWallets->first()->id ?? 'null' }} }">
        <h2 class="text-lg font-bold text-charcoal-900 dark:text-white mb-2">Deposit {{ $asset->name }} ({{ $asset->symbol }})</h2>
        <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-4">Send only {{ $asset->symbol }} ({{ $asset->network }} network) to the address below. Sending any other asset may result in permanent loss.</p>

        @if ($platformWallets->isEmpty())
            <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 p-4 text-sm text-amber-700 dark:text-amber-300">
                No receiving address has been configured for {{ $asset->symbol }} yet. Please contact support before sending funds.
            </div>
        @else
            @if ($platformWallets->count() > 1)
                <div class="mb-4">
                    <x-input-label value="Choose an address" />
                    <select x-model.number="selected" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                        @foreach ($platformWallets as $wallet)
                            <option value="{{ $wallet->id }}">{{ $wallet->label ?: 'Address '.$loop->iteration }}{{ $wallet->is_default ? ' (Default)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @foreach ($platformWallets as $wallet)
                <div x-show="selected === {{ $wallet->id }}" style="display:none">
                    <div class="rounded-xl bg-charcoal-50 dark:bg-charcoal-800 p-4 break-all font-mono text-sm">
                        {{ $wallet->wallet_address }}
                    </div>

                    @if ($wallet->memo_tag)
                        <div class="mt-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 p-3 text-sm">
                            <span class="text-charcoal-400">Memo/Tag (required):</span>
                            <span class="font-mono font-semibold ml-1">{{ $wallet->memo_tag }}</span>
                        </div>
                    @endif

                    <button type="button" onclick="navigator.clipboard.writeText('{{ $wallet->wallet_address }}')" class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold">
                        Copy Address
                    </button>
                </div>
            @endforeach
        @endif

        <p class="mt-4 text-xs text-charcoal-400">Deposits are usually credited after network confirmation. Contact support if your deposit hasn't arrived after the expected confirmation time.</p>
    </div>
</x-app-layout>
