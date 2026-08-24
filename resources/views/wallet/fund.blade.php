<x-app-layout>
    <x-slot name="title">Fund Fiat Wallet</x-slot>

    <div class="max-w-lg mx-auto rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-charcoal-900 dark:text-white mb-4">Fund Fiat Wallet</h2>

        <form method="POST" action="{{ route('wallet.fund.store') }}" class="space-y-4">
            @csrf
            <div>
                <x-input-label for="fiat_currency" value="Currency" />
                <select id="fiat_currency" name="fiat_currency" required class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                    @foreach ($fiatCurrencies as $fiat)
                        <option value="{{ $fiat->code }}">{{ $fiat->name }} ({{ $fiat->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="amount" value="Amount" />
                <x-text-input id="amount" name="amount" type="number" step="0.01" min="1" required class="mt-1 w-full" placeholder="e.g. 50000" />
                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
            </div>

            <div>
                <x-input-label value="Payment Method" />
                <div class="mt-2 space-y-2">
                    @foreach ($gateways as $gateway)
                        <label class="flex items-center gap-3 rounded-lg border border-charcoal-200 dark:border-charcoal-700 px-4 py-3 cursor-pointer hover:border-brand-400">
                            <input type="radio" name="gateway" value="{{ $gateway->slug }}" required class="text-brand-600 focus:ring-brand-500">
                            <span class="text-sm font-medium">{{ $gateway->name }}</span>
                        </label>
                    @endforeach
                    @if ($gateways->isEmpty())
                        <p class="text-sm text-charcoal-400">No payment gateways are currently active. Please contact support.</p>
                    @endif
                </div>
                <x-input-error :messages="$errors->get('gateway')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center py-3">Continue</x-primary-button>
        </form>
    </div>
</x-app-layout>
