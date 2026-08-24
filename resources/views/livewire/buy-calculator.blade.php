<div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">You receive</label>
            <div class="flex items-center gap-2">
                @php $selectedCrypto = $this->cryptoAssets->firstWhere('id', $cryptoAssetId); @endphp
                @if ($selectedCrypto)
                    <img src="{{ $selectedCrypto->logoUrl() }}" class="h-8 w-8 rounded-full flex-shrink-0" alt="">
                @endif
                <select wire:model.live="cryptoAssetId" name="crypto_asset_id" required class="w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Select crypto</option>
                    @foreach ($this->cryptoAssets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->symbol }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">Pay with</label>
            <div class="flex items-center gap-2">
                @php $selectedFiat = $this->fiatCurrencies->firstWhere('id', $fiatCurrencyId); @endphp
                @if ($selectedFiat)
                    <img src="{{ $selectedFiat->logoUrl() }}" class="h-8 w-8 rounded-full flex-shrink-0" alt="">
                @endif
                <select wire:model.live="fiatCurrencyId" name="fiat_currency_id" required class="w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Select currency</option>
                    @foreach ($this->fiatCurrencies as $fiat)
                        <option value="{{ $fiat->id }}">{{ $fiat->name }} ({{ $fiat->code }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">Amount to pay</label>
        <input type="number" step="0.01" min="1" wire:model.live.debounce.400ms="fiatAmount" name="fiat_amount" required placeholder="e.g. 50000" class="w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
    </div>

    <div class="rounded-xl bg-brand-50 dark:bg-charcoal-800 border border-brand-100 dark:border-charcoal-700 px-4 py-3 text-sm">
        @if ($this->quote && isset($this->quote['error']))
            <p class="text-red-600 dark:text-red-400">{{ $this->quote['error'] }}</p>
        @elseif ($this->quote)
            <div class="flex items-center justify-between">
                <span class="text-charcoal-500 dark:text-charcoal-400">You will receive approximately</span>
                <span class="font-bold text-brand-700 dark:text-brand-400">{{ $this->quote['crypto_amount'] }} {{ $this->quote['symbol'] }}</span>
            </div>
            <p class="text-xs text-charcoal-400 mt-1">Rate locked while you complete payment &middot; expires {{ $this->quote['rate']->expires_at->diffForHumans() }}</p>
        @else
            <p class="text-charcoal-400">Enter an amount to see how much crypto you'll receive.</p>
        @endif
    </div>
</div>
