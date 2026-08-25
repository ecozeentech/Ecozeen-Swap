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

            <div x-show="type === 'fiat'">
                <x-input-label value="Settlement Bank Account" />
                @if ($bankAccounts->isEmpty())
                    <p class="mt-1 text-sm text-amber-600">You have no bank accounts yet. <a href="{{ route('bank-accounts.index') }}" class="underline font-semibold">Add one first</a>.</p>
                @else
                    <select name="bank_account_id" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                        @foreach ($bankAccounts as $account)
                            <option value="{{ $account->id }}" @selected($account->is_default)>{{ $account->bank_name }} &middot; {{ $account->account_name }} ({{ $account->maskedAccountNumber() }})</option>
                        @endforeach
                    </select>
                @endif
                <x-input-error :messages="$errors->get('bank_account_id')" class="mt-2" />
            </div>

            <div x-show="type === 'crypto'" style="display:none">
                <x-input-label for="destination" value="Destination Wallet Address" />
                <x-text-input id="destination" name="destination" type="text" class="mt-1 w-full" placeholder="External crypto wallet address" />
                <x-input-error :messages="$errors->get('destination')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center py-3">Request Withdrawal</x-primary-button>
            <p class="text-xs text-charcoal-400">Withdrawals are reviewed by our team before funds are released.</p>
        </form>
    </div>
</x-app-layout>
