<div wire:poll.10s>
    @if ($errorMessage)
        <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300">
            {{ $errorMessage }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <livewire:admin.daily-rate-form />

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
            <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800 flex items-center justify-between">
                <h3 class="font-semibold">Active Rates</h3>
                <span class="flex items-center gap-1.5 text-[11px] text-charcoal-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse"></span>
                    Live — refreshes every 10s
                </span>
            </div>
            <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800 max-h-[28rem] overflow-y-auto">
                @forelse ($activeRates as $rate)
                    <div x-data="{ editing: false }" class="px-5 py-3" wire:key="active-{{ $rate->id }}">
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
                                    <x-input-label value="Crypto Asset" class="text-xs" />
                                    <select name="crypto_asset_id" class="mt-1 w-full text-sm rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white">
                                        @foreach ($this->cryptoAssets as $asset)
                                            <option value="{{ $asset->id }}" @selected($asset->id === $rate->crypto_asset_id)>{{ $asset->name }} ({{ $asset->symbol }}{{ $asset->network ? ' — '.$asset->network : '' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label value="Fiat Currency" class="text-xs" />
                                    <select name="fiat_currency_id" class="mt-1 w-full text-sm rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white">
                                        @foreach ($this->fiatCurrencies as $fiat)
                                            <option value="{{ $fiat->id }}" @selected($fiat->id === $rate->fiat_currency_id)>{{ $fiat->name }} ({{ $fiat->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <p class="col-span-2 text-[11px] text-charcoal-400 -mt-1">Changing the pair re-points this rate entirely — the previous pair will show no active rate until a new one is set.</p>
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
                                <button type="button" wire:click="toggleActive({{ $rate->id }})" wire:confirm="Deactivate this rate?" class="text-xs font-semibold text-amber-600 hover:underline">Deactivate</button>
                                <button type="button" wire:click="destroy({{ $rate->id }})" wire:confirm="Delete this rate permanently?" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
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
                    <tr wire:key="history-{{ $rate->id }}">
                        <td class="px-5 py-3 font-semibold">{{ $rate->cryptoAsset->symbol }}/{{ $rate->fiatCurrency->code }}</td>
                        <td class="px-5 py-3">{{ number_format($rate->buy_rate, 2) }}</td>
                        <td class="px-5 py-3">{{ number_format($rate->sell_rate, 2) }}</td>
                        <td class="px-5 py-3 text-charcoal-400">{{ $rate->createdBy->username ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $rate->is_active && ! $rate->isExpired() ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600' }}">
                                {{ $rate->is_active && ! $rate->isExpired() ? 'Active' : 'Expired' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-charcoal-400">{{ $rate->expires_at->format('M d, Y H:i') }}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            @if ($rate->isExpired() || ! $rate->is_active)
                                <button type="button" wire:click="toggleRenew({{ $rate->id }})" class="text-xs font-semibold text-brand-600 hover:underline mr-3">
                                    {{ $renewingRateId === $rate->id ? 'Close' : 'Renew' }}
                                </button>
                            @endif
                            <button type="button" wire:click="destroy({{ $rate->id }})" wire:confirm="Delete this rate permanently?" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                        </td>
                    </tr>
                    @if ($renewingRateId === $rate->id)
                        <tr wire:key="renew-row-{{ $rate->id }}">
                            <td colspan="7" class="px-5 pb-4 bg-charcoal-50 dark:bg-charcoal-800/40">
                                <form wire:submit="renew({{ $rate->id }})" class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 items-end">
                                    <div>
                                        <x-input-label value="New Buy Rate" class="text-xs" />
                                        <input type="number" step="0.00000001" wire:model="renewBuyRate" class="mt-1 w-full text-sm rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white">
                                        @error('renewBuyRate') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <x-input-label value="New Sell Rate" class="text-xs" />
                                        <input type="number" step="0.00000001" wire:model="renewSellRate" class="mt-1 w-full text-sm rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white">
                                        @error('renewSellRate') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <x-input-label value="Valid For (hours)" class="text-xs" />
                                        <input type="number" min="1" wire:model="renewHoursValid" class="mt-1 w-full text-sm rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white">
                                        @error('renewHoursValid') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                        <div class="flex gap-1 mt-1.5 flex-wrap">
                                            @foreach ([['24', '1d'], ['168', '7d'], ['720', '30d'], ['8760', '1y']] as [$hours, $label])
                                                <button type="button" wire:click="$set('renewHoursValid', {{ $hours }})" class="text-[10px] font-semibold px-2 py-0.5 rounded-full border border-charcoal-200 dark:border-charcoal-700 text-charcoal-500 hover:border-brand-400">{{ $label }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-secondary-button type="button" wire:click="toggleRenew({{ $rate->id }})" class="!text-[10px] !px-3 !py-1.5">Cancel</x-secondary-button>
                                        <x-primary-button type="submit" class="!text-[10px] !px-3 !py-1.5">
                                            <span wire:loading.remove wire:target="renew({{ $rate->id }})">Renew Rate</span>
                                            <span wire:loading wire:target="renew({{ $rate->id }})">Renewing…</span>
                                        </x-primary-button>
                                    </div>
                                </form>
                                <p class="text-[11px] text-charcoal-400 pb-2 pt-1">Reuses this same rate record with a fresh window instead of creating a new one — no need to delete expired rates, and no limit on how many hours/days it can run for.</p>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
