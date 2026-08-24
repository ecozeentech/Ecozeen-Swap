<div class="space-y-4">
    @if ($successMessage)
        <div class="rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300">
            {{ $errorMessage }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">From</label>
            <select wire:model="fromAssetId" class="w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                <option value="">Select asset</option>
                @foreach ($this->cryptoAssets as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->symbol }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">To</label>
            <select wire:model="toAssetId" class="w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                <option value="">Select asset</option>
                @foreach ($this->cryptoAssets as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->symbol }})</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-charcoal-700 dark:text-charcoal-300 mb-1">Amount</label>
        <input type="number" step="0.00000001" min="0.00000001" wire:model="amount" placeholder="e.g. 0.1" class="w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
    </div>

    @if (! $lockedQuote)
        <button wire:click="getQuote" type="button" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg shadow-sm">
            <span wire:loading.remove wire:target="getQuote">Get Swap Quote</span>
            <span wire:loading wire:target="getQuote">Calculating...</span>
        </button>
    @else
        <div x-data="{
                expires: new Date('{{ $lockedQuote['expires_at'] }}').getTime(),
                remaining: 300,
                tick() {
                    this.remaining = Math.max(0, Math.floor((this.expires - Date.now()) / 1000));
                }
            }" x-init="tick(); let i = setInterval(() => tick(), 1000)" class="rounded-xl border border-brand-200 dark:border-brand-800 bg-brand-50 dark:bg-charcoal-800 px-4 py-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-charcoal-500 dark:text-charcoal-400">You send</span>
                <span class="font-bold text-charcoal-900 dark:text-white">{{ $lockedQuote['from_amount'] }} {{ $lockedQuote['from_symbol'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-charcoal-500 dark:text-charcoal-400">You receive</span>
                <span class="font-bold text-brand-700 dark:text-brand-400">{{ $lockedQuote['to_amount'] }} {{ $lockedQuote['to_symbol'] }}</span>
            </div>
            <div class="flex items-center justify-between text-xs text-charcoal-400">
                <span>Rate locked for</span>
                <span x-text="remaining + 's'" class="font-mono"></span>
            </div>
            <div class="flex gap-2">
                <button wire:click="confirmSwap" type="button" class="flex-1 inline-flex justify-center items-center px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg shadow-sm">
                    <span wire:loading.remove wire:target="confirmSwap">Confirm Swap</span>
                    <span wire:loading wire:target="confirmSwap">Processing...</span>
                </button>
                <button wire:click="$set('lockedQuote', null)" type="button" class="px-4 py-2.5 border border-charcoal-200 dark:border-charcoal-600 text-charcoal-600 dark:text-charcoal-300 text-sm font-semibold rounded-lg">
                    Cancel
                </button>
            </div>
        </div>
    @endif
</div>
