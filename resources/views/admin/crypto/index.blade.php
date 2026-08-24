<x-admin-layout>
    <x-slot name="title">Crypto Assets</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <h3 class="font-semibold mb-4">Add Crypto Asset</h3>
            <form method="POST" action="{{ route('admin.crypto.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <x-input-label value="Name" />
                    <x-text-input name="name" required class="mt-1 w-full" placeholder="Bitcoin" />
                </div>
                <div>
                    <x-input-label value="Symbol" />
                    <x-text-input name="symbol" required class="mt-1 w-full" placeholder="BTC" />
                </div>
                <div>
                    <x-input-label value="Network" />
                    <x-text-input name="network" class="mt-1 w-full" placeholder="Bitcoin / ERC20 / TRC20" />
                </div>
                <div>
                    <x-input-label value="Decimal Places" />
                    <x-text-input name="decimal_places" type="number" value="8" required class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="Logo" />
                    <input type="file" name="logo" accept="image/*" class="mt-1 w-full text-sm">
                </div>
                <x-primary-button class="w-full justify-center py-2.5">Add Asset</x-primary-button>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-left text-charcoal-400 text-xs uppercase bg-charcoal-50 dark:bg-charcoal-800/50">
                    <tr><th class="px-5 py-3">Asset</th><th class="px-5 py-3">Network</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                    @foreach ($assets as $asset)
                        <tr x-data="{ editing: false }">
                            <td colspan="4" class="p-0">
                                <div class="flex items-center justify-between px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $asset->logoUrl() }}" alt="{{ $asset->symbol }}" class="h-8 w-8 rounded-full object-cover bg-charcoal-100">
                                        <div>
                                            <p class="font-semibold">{{ $asset->name }} <span class="text-charcoal-400">({{ $asset->symbol }})</span></p>
                                            <p class="text-xs text-charcoal-400">{{ $asset->network ?: 'No network set' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $asset->is_active ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600' }}">{{ $asset->is_active ? 'Active' : 'Inactive' }}</span>
                                        <button type="button" @click="editing = !editing" class="text-xs font-semibold text-brand-600 hover:underline">Edit</button>
                                        <form method="POST" action="{{ route('admin.crypto.toggle', $asset) }}">
                                            @csrf
                                            <button type="submit" class="text-xs font-semibold text-amber-600 hover:underline">{{ $asset->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.crypto.destroy', $asset) }}" onsubmit="return confirm('Delete {{ $asset->symbol }} permanently? This cannot be undone.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                <div x-show="editing" x-transition style="display:none" class="px-5 pb-4 bg-charcoal-50 dark:bg-charcoal-800/40">
                                    <form method="POST" action="{{ route('admin.crypto.update', $asset) }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="is_active" value="{{ $asset->is_active ? 1 : 0 }}">
                                        <div>
                                            <x-input-label value="Name" />
                                            <x-text-input name="name" value="{{ $asset->name }}" required class="mt-1 w-full" />
                                        </div>
                                        <div>
                                            <x-input-label value="Symbol" />
                                            <x-text-input name="symbol" value="{{ $asset->symbol }}" required class="mt-1 w-full" />
                                        </div>
                                        <div>
                                            <x-input-label value="Network" />
                                            <x-text-input name="network" value="{{ $asset->network }}" class="mt-1 w-full" />
                                        </div>
                                        <div>
                                            <x-input-label value="Decimal Places" />
                                            <x-text-input name="decimal_places" type="number" value="{{ $asset->decimal_places }}" required class="mt-1 w-full" />
                                        </div>
                                        <div class="sm:col-span-2">
                                            <x-input-label value="Replace Logo" />
                                            <input type="file" name="logo" accept="image/*" class="mt-1 w-full text-sm">
                                        </div>
                                        <div class="sm:col-span-2 flex justify-end gap-2">
                                            <x-secondary-button type="button" @click="editing = false">Cancel</x-secondary-button>
                                            <x-primary-button type="submit">Save Changes</x-primary-button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
