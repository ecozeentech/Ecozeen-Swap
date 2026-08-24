<x-app-layout>
    <x-slot name="title">Withdraw</x-slot>

    <div class="max-w-lg mx-auto rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-charcoal-900 dark:text-white mb-4">Withdraw Funds</h2>

        <form method="POST" action="{{ route('wallet.withdraw.store') }}" x-data="{ type: 'fiat' }" class="space-y-4">
            @csrf
            <div>
                <x-input-label value="Withdraw" />
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <label class="flex items-center justify-center gap-2 rounded-lg border px-4 py-2 cursor-pointer" :class="type === 'fiat' ? 'border-brand-500 bg-brand-50 dark:bg-charcoal-800' : 'border-charcoal-200 dark:border-charcoal-700'">
                        <input type="radio" name="currency_type" value="fiat" x-model="type" class="hidden">
                        <span class="text-sm font-medium">Fiat to Bank</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 rounded-lg border px-4 py-2 cursor-pointer" :class="type === 'crypto' ? 'border-brand-500 bg-brand-50 dark:bg-charcoal-800' : 'border-charcoal-200 dark:border-charcoal-700'">
                        <input type="radio" name="currency_type" value="crypto" x-model="type" class="hidden">
                        <span class="text-sm font-medium">Crypto to Address</span>
                    </label>
                </div>
            </div>

            <div>
                <x-input-label for="currency_code" value="Currency" />
                <select id="currency_code" name="currency_code" required class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                    <template x-if="type === 'fiat'">
                        <template x-for="fiat in {{ $fiatCurrencies->pluck('code')->toJson() }}">
                            <option :value="fiat" x-text="fiat"></option>
                        </template>
                    </template>
                    <template x-if="type === 'crypto'">
                        <template x-for="crypto in {{ $cryptoAssets->pluck('symbol')->toJson() }}">
                            <option :value="crypto" x-text="crypto"></option>
                        </template>
                    </template>
                </select>
            </div>

            <div>
                <x-input-label for="amount" value="Amount" />
                <x-text-input id="amount" name="amount" type="number" step="0.00000001" min="0.00000001" required class="mt-1 w-full" />
                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="destination" value="Destination (bank account or wallet address)" />
                <x-text-input id="destination" name="destination" type="text" required class="mt-1 w-full" placeholder="Account number / crypto address" />
                <x-input-error :messages="$errors->get('destination')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center py-3">Request Withdrawal</x-primary-button>
            <p class="text-xs text-charcoal-400">Withdrawals are reviewed by our team before funds are released.</p>
        </form>
    </div>
</x-app-layout>
