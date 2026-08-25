<x-admin-layout>
    <x-slot name="title">Crypto Wallets</x-slot>

    <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-6">
        These are the platform's own receiving addresses. Every user depositing or selling a coin sends to the
        same address(es) shown here &mdash; there is no per-user generated address.
    </p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <h3 class="font-semibold mb-4">Add Wallet Address</h3>
            <form method="POST" action="{{ route('admin.crypto-wallets.store') }}" class="space-y-3">
                @csrf
                <div>
                    <x-input-label value="Crypto Asset" />
                    <select name="crypto_asset_id" required class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                        @foreach ($cryptoAssets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->symbol }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="Wallet Address" />
                    <x-text-input name="wallet_address" required class="mt-1 w-full font-mono text-sm" placeholder="bc1q..." />
                </div>
                <div>
                    <x-input-label value="Label" />
                    <x-text-input name="label" class="mt-1 w-full" placeholder="Hot Wallet / Cold Storage" />
                </div>
                <div>
                    <x-input-label value="Memo / Tag (if required)" />
                    <x-text-input name="memo_tag" class="mt-1 w-full" />
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_default" value="1" class="rounded border-charcoal-300 text-brand-600 focus:ring-brand-500">
                    Set as default for this asset
                </label>
                <x-primary-button class="w-full justify-center py-2.5">Add Address</x-primary-button>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm divide-y divide-charcoal-100 dark:divide-charcoal-800">
            @forelse ($wallets as $wallet)
                <div x-data="{ editing: false }" class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ $wallet->cryptoAsset->logoUrl() }}" class="h-8 w-8 rounded-full" alt="">
                            <div>
                                <p class="font-semibold text-sm">{{ $wallet->cryptoAsset->symbol }} &middot; {{ $wallet->label ?: 'Unlabeled' }}</p>
                                <p class="text-xs text-charcoal-400 font-mono break-all">{{ $wallet->wallet_address }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if ($wallet->is_default)
                                <span class="text-[10px] font-semibold uppercase tracking-wide bg-brand-100 text-brand-700 px-2 py-0.5 rounded-full">Default</span>
                            @endif
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $wallet->is_active ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600' }}">{{ $wallet->is_active ? 'Active' : 'Inactive' }}</span>
                            <button type="button" @click="editing = !editing" class="text-xs font-semibold text-brand-600 hover:underline">Edit</button>
                        </div>
                    </div>

                    <div x-show="editing" x-transition style="display:none" class="mt-4 rounded-lg bg-charcoal-50 dark:bg-charcoal-800/40 p-4 space-y-3">
                        <form method="POST" action="{{ route('admin.crypto-wallets.update', $wallet) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @csrf @method('PUT')
                            <input type="hidden" name="crypto_asset_id" value="{{ $wallet->crypto_asset_id }}">
                            <input type="hidden" name="is_active" value="{{ $wallet->is_active ? 1 : 0 }}">
                            <div class="sm:col-span-2">
                                <x-input-label value="Wallet Address" />
                                <x-text-input name="wallet_address" value="{{ $wallet->wallet_address }}" required class="mt-1 w-full font-mono text-sm" />
                            </div>
                            <div>
                                <x-input-label value="Label" />
                                <x-text-input name="label" value="{{ $wallet->label }}" class="mt-1 w-full" />
                            </div>
                            <div>
                                <x-input-label value="Memo / Tag" />
                                <x-text-input name="memo_tag" value="{{ $wallet->memo_tag }}" class="mt-1 w-full" />
                            </div>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="is_default" value="1" @checked($wallet->is_default) class="rounded border-charcoal-300 text-brand-600 focus:ring-brand-500">
                                Default for {{ $wallet->cryptoAsset->symbol }}
                            </label>
                            <div class="sm:col-span-2">
                                <x-primary-button type="submit" class="!text-xs !px-4 !py-2">Save Changes</x-primary-button>
                            </div>
                        </form>
                        <div class="flex gap-3 pt-2 border-t border-charcoal-100 dark:border-charcoal-700">
                            <form method="POST" action="{{ route('admin.crypto-wallets.toggle', $wallet) }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-amber-600 hover:underline">{{ $wallet->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.crypto-wallets.destroy', $wallet) }}" onsubmit="return confirm('Delete this address?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="p-6 text-sm text-charcoal-400 text-center">No wallet addresses configured yet.</p>
            @endforelse
        </div>
    </div>
</x-admin-layout>
