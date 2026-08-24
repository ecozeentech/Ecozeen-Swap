<x-admin-layout>
    <x-slot name="title">Daily Rates</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <livewire:admin.daily-rate-form />

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
            <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800"><h3 class="font-semibold">Active Rates</h3></div>
            <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800 max-h-[28rem] overflow-y-auto">
                @forelse ($activeRates as $rate)
                    <div class="px-5 py-3 flex items-center justify-between text-sm">
                        <div>
                            <p class="font-semibold">{{ $rate->cryptoAsset->symbol }}/{{ $rate->fiatCurrency->code }}</p>
                            <p class="text-xs text-charcoal-400">Buy {{ number_format($rate->buy_rate, 2) }} &middot; Sell {{ number_format($rate->sell_rate, 2) }}</p>
                        </div>
                        <span class="text-xs text-charcoal-400">expires {{ $rate->expires_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-charcoal-400 text-center">No active rates.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm overflow-x-auto">
        <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800"><h3 class="font-semibold">Rate History</h3></div>
        <table class="w-full text-sm">
            <thead class="text-left text-charcoal-400 text-xs uppercase">
                <tr>
                    <th class="px-5 py-3">Pair</th>
                    <th class="px-5 py-3">Buy</th>
                    <th class="px-5 py-3">Sell</th>
                    <th class="px-5 py-3">Set By</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Expires</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                @foreach ($history as $rate)
                    <tr>
                        <td class="px-5 py-3 font-semibold">{{ $rate->cryptoAsset->symbol }}/{{ $rate->fiatCurrency->code }}</td>
                        <td class="px-5 py-3">{{ number_format($rate->buy_rate, 2) }}</td>
                        <td class="px-5 py-3">{{ number_format($rate->sell_rate, 2) }}</td>
                        <td class="px-5 py-3 text-charcoal-400">{{ $rate->createdBy->username ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $rate->is_active && ! $rate->isExpired() ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600' }}">
                                {{ $rate->is_active && ! $rate->isExpired() ? 'Active' : 'Expired' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-charcoal-400">{{ $rate->expires_at->format('M d, H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-layout>
