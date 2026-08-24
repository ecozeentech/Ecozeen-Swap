<div wire:poll.30s="$refresh" class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
    <div class="flex items-center justify-between px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800">
        <h3 class="font-semibold text-charcoal-900 dark:text-white">24-Hour Rate Board</h3>
        <span class="text-xs text-charcoal-400">Set by Ecozeen Swap</span>
    </div>

    <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800 max-h-96 overflow-y-auto">
        @forelse ($rates as $rate)
            <div class="flex items-center justify-between gap-4 px-5 py-3" x-data="{
                    expires: new Date('{{ $rate->expires_at->toIso8601String() }}').getTime(),
                    remaining: '',
                    tick() {
                        const diff = this.expires - Date.now();
                        if (diff <= 0) { this.remaining = 'Expired'; return; }
                        const h = Math.floor(diff / 3600000);
                        const m = Math.floor((diff % 3600000) / 60000);
                        const s = Math.floor((diff % 60000) / 1000);
                        this.remaining = h.toString().padStart(2,'0') + ':' + m.toString().padStart(2,'0') + ':' + s.toString().padStart(2,'0');
                    }
                }" x-init="tick(); setInterval(() => tick(), 1000)">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-brand-50 dark:bg-charcoal-800 flex items-center justify-center font-bold text-brand-600 dark:text-brand-400 text-xs">
                        {{ $rate->cryptoAsset->symbol }}
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-charcoal-900 dark:text-white">{{ $rate->cryptoAsset->symbol }}/{{ $rate->fiatCurrency->code }}</p>
                        <p class="text-xs text-charcoal-400">Buy {{ number_format($rate->buy_rate, 2) }} &middot; Sell {{ number_format($rate->sell_rate, 2) }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-charcoal-400">Resets in</p>
                    <p class="font-mono font-semibold text-sm text-brand-600 dark:text-brand-400" x-text="remaining"></p>
                </div>
            </div>
        @empty
            <p class="px-5 py-6 text-sm text-charcoal-400 text-center">No active rates yet. Please check back shortly.</p>
        @endforelse
    </div>
</div>
