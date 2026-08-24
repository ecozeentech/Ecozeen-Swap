<x-admin-layout>
    <x-slot name="title">Daily Rates</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <livewire:admin.daily-rate-form />

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
            <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800"><h3 class="font-semibold">Active Rates</h3></div>
            <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800 max-h-[28rem] overflow-y-auto">
                @forelse ($activeRates as $rate)
                    <div x-data="{ editing: false }" class="px-5 py-3">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <img src="{{ $rate->cryptoAsset->logoUrl() }}" class="h-6 w-6 rounded-full" alt="">
                                <div>
                                    <p class="font-semibold">{{ $rate->cryptoAsset->symbol }}/{{ $rate->fiatCurrency->code }}</p>
                                    <p class="text-xs text-charcoal-400">Buy {{ number_format($rate->buy_rate, 2) }} &middot; Sell {{ number_format($rate->sell_rate, 2) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-charcoal-400">expires {{ $rate->expires_at->diffForHumans() }}</span>
                                <button type="button" @click="editing = !editing" class="text-xs font-semibold text-brand-600 hover:underline">Edit</button>
                            </div>
                        </div>
                        <div x-show="editing" x-transition style="display:none" class="mt-3 rounded-lg bg-charcoal-50 dark:bg-charcoal-800/40 p-3">
                            <form method="POST" action="{{ route('admin.rates.update', $rate) }}" class="grid grid-cols-2 gap-2">
                                @csrf @method('PUT')
                                <input type="hidden" name="is_active" value="1">
                                <div>
                                    <x-input-label value="Buy Rate" class="text-xs" />
                                    <x-text-input name="buy_rate" type="number" step="0.00000001" value="{{ $rate->buy_rate }}" class="mt-1 w-full text-sm" />
                                </div>
                                <div>
                                    <x-input-label value="Sell Rate" class="text-xs" />
                                    <x-text-input name="sell_rate" type="number" step="0.00000001" value="{{ $rate->sell_rate }}" class="mt-1 w-full text-sm" />
                                </div>
                                <div class="col-span-2 flex justify-end gap-2 mt-1">
                                    <x-secondary-button type="button" @click="editing = false" class="!text-[10px] !px-3 !py-1.5">Cancel</x-secondary-button>
                                    <x-primary-button type="submit" class="!text-[10px] !px-3 !py-1.5">Save</x-primary-button>
                                </div>
                            </form>
                            <div class="flex justify-end gap-3 mt-2">
                                <form method="POST" action="{{ route('admin.rates.toggle', $rate) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-amber-600 hover:underline">Deactivate</button>
                                </form>
                                <form method="POST" action="{{ route('admin.rates.destroy', $rate) }}" onsubmit="return confirm('Delete this rate permanently?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        </div>
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
            <thead class="text-left text-charcoal-400 text-xs uppercase bg-charcoal-50 dark:bg-charcoal-800/50">
                <tr>
                    <th class="px-5 py-3">Pair</th>
                    <th class="px-5 py-3">Buy</th>
                    <th class="px-5 py-3">Sell</th>
                    <th class="px-5 py-3">Set By</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Expires</th>
                    <th class="px-5 py-3"></th>
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
                        <td class="px-5 py-3 text-right">
                            <form method="POST" action="{{ route('admin.rates.destroy', $rate) }}" onsubmit="return confirm('Delete this rate permanently?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-layout>
