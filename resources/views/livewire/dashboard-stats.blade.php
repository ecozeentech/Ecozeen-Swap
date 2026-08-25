<div wire:poll.60s="$refresh" class="space-y-6">
    <div class="rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 text-white p-6 shadow-lg relative overflow-hidden" x-data="{ hidden: false }">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-brand-100">Total Portfolio Balance</p>
                <p class="text-3xl sm:text-4xl font-extrabold mt-1" x-show="!hidden">{{ $displayCurrency->symbol ?? '$' }}{{ number_format($portfolioDisplay, 2) }}</p>
                <p class="text-3xl sm:text-4xl font-extrabold mt-1" x-show="hidden" style="display:none">••••••</p>
                @if (($displayCurrency->code ?? 'USD') !== 'USD')
                    <p class="text-xs text-brand-100 mt-0.5">&asymp; ${{ number_format($portfolioUsd, 2) }} USD</p>
                @endif
            </div>
            <div class="flex items-center gap-1.5">
                <select wire:model.live="selectedCurrency" class="rounded-lg bg-white/10 border-white/20 text-white text-xs py-1.5 pl-2 pr-6 focus:ring-white/40 focus:border-white/40 [&>option]:text-charcoal-900">
                    @foreach ($availableCurrencies as $currency)
                        <option value="{{ $currency->code }}">{{ $currency->code }}</option>
                    @endforeach
                </select>
                <button type="button" @click="hidden = !hidden" class="rounded-full p-1.5 bg-white/10 hover:bg-white/20" aria-label="Toggle balance visibility">
                    <svg x-show="!hidden" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="hidden" style="display:none" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.774 3.162 10.066 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L9.88 9.88" /></svg>
                </button>
            </div>
        </div>

        <div class="mt-4 h-12 w-full max-w-xs" aria-hidden="true">
            <svg viewBox="0 0 200 50" class="w-full h-full" preserveAspectRatio="none">
                <polyline points="0,40 25,32 50,36 75,20 100,26 125,14 150,18 175,6 200,10" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <polyline points="0,40 25,32 50,36 75,20 100,26 125,14 150,18 175,6 200,10 200,50 0,50" fill="rgba(255,255,255,0.12)" stroke="none" />
            </svg>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('wallet.fund') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm font-semibold">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0 0l-6-6m6 6l6-6" /></svg>
                Deposit
            </a>
            <a href="{{ route('wallet.withdraw') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm font-semibold">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20V4m0 0l6 6m-6-6l-6 6" /></svg>
                Withdraw
            </a>
            <a href="{{ route('swap.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm font-semibold">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" /></svg>
                Transfer
            </a>
        </div>
    </div>

    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800">
            <h3 class="font-semibold text-charcoal-900 dark:text-white">Recent Transactions</h3>
            <a href="{{ route('wallet.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">View All</a>
        </div>
        <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
            @forelse ($recentTransactions as $tx)
                <div class="flex items-center gap-3 px-5 py-3">
                    <span @class([
                        'h-9 w-9 rounded-full flex items-center justify-center flex-shrink-0',
                        'bg-green-50 text-green-600 dark:bg-green-900/30' => in_array($tx->type, ['deposit', 'buy']),
                        'bg-red-50 text-red-500 dark:bg-red-900/30' => in_array($tx->type, ['withdrawal', 'sell']),
                        'bg-brand-50 text-brand-600 dark:bg-charcoal-800' => in_array($tx->type, ['swap', 'giftcard', 'invoice', 'fee']),
                    ])>
                        @if (in_array($tx->type, ['deposit', 'buy']))
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0 0l-6-6m6 6l6-6" /></svg>
                        @elseif (in_array($tx->type, ['withdrawal', 'sell']))
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20V4m0 0l6 6m-6-6l-6 6" /></svg>
                        @else
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" /></svg>
                        @endif
                    </span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-charcoal-900 dark:text-white capitalize">{{ $tx->type }} &middot; {{ $tx->currency_code }}</p>
                        <p class="text-xs text-charcoal-400">{{ $tx->created_at->diffForHumans() }} &middot; {{ $tx->reference }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-charcoal-900 dark:text-white">{{ number_format($tx->amount, 4) }}</p>
                        <span @class([
                            'text-xs font-medium px-2 py-0.5 rounded-full',
                            'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' => $tx->status === 'completed',
                            'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' => in_array($tx->status, ['pending', 'processing']),
                            'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => in_array($tx->status, ['failed', 'cancelled']),
                        ])>{{ ucfirst($tx->status) }}</span>
                    </div>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-charcoal-400 text-center">No transactions yet. Start by funding your wallet.</p>
            @endforelse
        </div>
    </div>
</div>
