<div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
    <h3 class="font-semibold text-charcoal-900 dark:text-white mb-4">Set a New 24-Hour Rate</h3>

    @if ($successMessage)
        <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-4 py-2 text-sm text-green-700 dark:text-green-300">
            {{ $successMessage }}
        </div>
    @endif

    <form wire:submit="setRate" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">Crypto Asset</label>
            <select wire:model.live="cryptoAssetId" class="w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                <option value="">Select asset</option>
                @foreach ($this->cryptoAssets as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->symbol }})</option>
                @endforeach
            </select>
            @error('cryptoAssetId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">Fiat Currency</label>
            <select wire:model.live="fiatCurrencyId" class="w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                <option value="">Select currency</option>
                @foreach ($this->fiatCurrencies as $fiat)
                    <option value="{{ $fiat->id }}">{{ $fiat->name }} ({{ $fiat->code }})</option>
                @endforeach
            </select>
            @error('fiatCurrencyId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        @if ($this->currentRate)
            <div class="sm:col-span-2 rounded-lg bg-charcoal-50 dark:bg-charcoal-800 px-4 py-2 text-xs text-charcoal-600 dark:text-charcoal-300">
                Current active rate: Buy {{ number_format($this->currentRate->buy_rate, 2) }} / Sell {{ number_format($this->currentRate->sell_rate, 2) }}
                — expires {{ $this->currentRate->expires_at->diffForHumans() }}. Setting a new rate will immediately replace it.
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">Buy Rate (platform buys from user)</label>
            <input type="number" step="0.00000001" wire:model="buyRate" class="w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
            @error('buyRate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">Sell Rate (platform sells to user)</label>
            <input type="number" step="0.00000001" wire:model="sellRate" class="w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
            @error('sellRate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">Valid For (hours)</label>
            <input type="number" wire:model="hoursValid" min="1" class="w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
            <div class="flex gap-1.5 mt-2 flex-wrap">
                @foreach ([['24', '1 day'], ['72', '3 days'], ['168', '7 days'], ['720', '30 days'], ['2160', '90 days'], ['8760', '1 year']] as [$hours, $label])
                    <button type="button" wire:click="$set('hoursValid', {{ $hours }})" class="text-[11px] font-semibold px-2.5 py-1 rounded-full border {{ (string) $hoursValid === $hours ? 'bg-brand-500 text-white border-brand-500' : 'border-charcoal-200 dark:border-charcoal-700 text-charcoal-500 hover:border-brand-400' }}">{{ $label }}</button>
                @endforeach
            </div>
            <p class="text-[11px] text-charcoal-400 mt-1">No upper limit — enter any number of hours, e.g. type a larger value for a longer run.</p>
            @error('hoursValid') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2 flex justify-end">
            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg shadow-sm">
                <span wire:loading.remove wire:target="setRate">Set 24-Hour Rate</span>
                <span wire:loading wire:target="setRate">Saving...</span>
            </button>
        </div>
    </form>
</div>
