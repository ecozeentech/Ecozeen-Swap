<div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">You are selling</label>
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
            <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">Receive payout in</label>
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
        <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">Amount to sell</label>
        <input type="number" step="0.00000001" min="0.00000001" wire:model.live.debounce.400ms="cryptoAmount" name="crypto_amount" required placeholder="e.g. 0.05" class="w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
    </div>

    <div class="rounded-xl bg-brand-50 dark:bg-charcoal-800 border border-brand-100 dark:border-charcoal-700 px-4 py-3 text-sm">
        @if ($this->quote && isset($this->quote['error']))
            <p class="text-red-600 dark:text-red-400">{{ $this->quote['error'] }}</p>
        @elseif ($this->quote)
            <div class="flex items-center justify-between">
                <span class="text-charcoal-500 dark:text-charcoal-400">You will be paid approximately</span>
                <span class="font-bold text-brand-700 dark:text-brand-400">{{ number_format($this->quote['fiat_amount'], 2) }} {{ $this->quote['code'] }}</span>
            </div>
            <p class="text-xs text-charcoal-400 mt-1">Payout is released after Ecozeen Swap confirms receipt of your crypto.</p>
        @else
            <p class="text-charcoal-400">Enter an amount to see your estimated payout.</p>
        @endif
    </div>
</div>
