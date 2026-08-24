<x-app-layout>
    <x-slot name="title">New Invoice</x-slot>

    <div class="max-w-lg mx-auto rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-charcoal-900 dark:text-white mb-4">Create Crypto Invoice</h2>

        <form method="POST" action="{{ route('invoices.store') }}" class="space-y-4">
            @csrf
            <div>
                <x-input-label for="crypto_asset_id" value="Crypto Asset" />
                <select id="crypto_asset_id" name="crypto_asset_id" required class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                    @foreach ($cryptoAssets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->symbol }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="amount_crypto" value="Amount" />
                <x-text-input id="amount_crypto" name="amount_crypto" type="number" step="0.00000001" min="0.00000001" required class="mt-1 w-full" />
                <x-input-error :messages="$errors->get('amount_crypto')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="recipient_email" value="Recipient Email" />
                <x-text-input id="recipient_email" name="recipient_email" type="email" required class="mt-1 w-full" />
                <x-input-error :messages="$errors->get('recipient_email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="recipient_name" value="Recipient Name (optional)" />
                <x-text-input id="recipient_name" name="recipient_name" type="text" class="mt-1 w-full" />
            </div>

            <div>
                <x-input-label for="description" value="Description (optional)" />
                <textarea id="description" name="description" rows="3" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500"></textarea>
            </div>

            <div>
                <x-input-label for="due_date" value="Due Date (optional)" />
                <x-text-input id="due_date" name="due_date" type="date" class="mt-1 w-full" />
            </div>

            <x-primary-button class="w-full justify-center py-3">Create &amp; Send Invoice</x-primary-button>
        </form>
    </div>
</x-app-layout>
