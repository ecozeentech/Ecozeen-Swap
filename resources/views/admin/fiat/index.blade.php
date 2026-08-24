<x-admin-layout>
    <x-slot name="title">Fiat Currencies</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <h3 class="font-semibold mb-4">Add Fiat Currency</h3>
            <form method="POST" action="{{ route('admin.fiat.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <x-input-label value="Code" />
                    <x-text-input name="code" required maxlength="6" class="mt-1 w-full" placeholder="NGN" />
                </div>
                <div>
                    <x-input-label value="Name" />
                    <x-text-input name="name" required class="mt-1 w-full" placeholder="Nigerian Naira" />
                </div>
                <div>
                    <x-input-label value="Symbol" />
                    <x-text-input name="symbol" required class="mt-1 w-full" placeholder="₦" />
                </div>
                <div>
                    <x-input-label value="Exchange Rate to USD" />
                    <x-text-input name="exchange_rate_to_usd" type="number" step="0.00000001" required class="mt-1 w-full" placeholder="0.00062" />
                </div>
                <div>
                    <x-input-label value="Flag / Logo" />
                    <input type="file" name="logo" accept="image/*" class="mt-1 w-full text-sm">
                </div>
                <x-primary-button class="w-full justify-center py-2.5">Add Currency</x-primary-button>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-left text-charcoal-400 text-xs uppercase bg-charcoal-50 dark:bg-charcoal-800/50">
                    <tr><th class="px-5 py-3">Currency</th><th class="px-5 py-3">Rate to USD</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                    @foreach ($currencies as $fiat)
                        <tr x-data="{ editing: false }">
                            <td colspan="4" class="p-0">
                                <div class="flex items-center justify-between px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $fiat->logoUrl() }}" alt="{{ $fiat->code }}" class="h-8 w-8 rounded-full object-cover bg-charcoal-100">
                                        <div>
                                            <p class="font-semibold">{{ $fiat->name }} <span class="text-charcoal-400">({{ $fiat->code }})</span></p>
                                            <p class="text-xs text-charcoal-400">{{ $fiat->exchange_rate_to_usd }} USD</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $fiat->is_active ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600' }}">{{ $fiat->is_active ? 'Active' : 'Inactive' }}</span>
                                        <button type="button" @click="editing = !editing" class="text-xs font-semibold text-brand-600 hover:underline">Edit</button>
                                        <form method="POST" action="{{ route('admin.fiat.toggle', $fiat) }}">
                                            @csrf
                                            <button type="submit" class="text-xs font-semibold text-amber-600 hover:underline">{{ $fiat->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.fiat.destroy', $fiat) }}" onsubmit="return confirm('Delete {{ $fiat->code }} permanently? This cannot be undone.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                <div x-show="editing" x-transition style="display:none" class="px-5 pb-4 bg-charcoal-50 dark:bg-charcoal-800/40">
                                    <form method="POST" action="{{ route('admin.fiat.update', $fiat) }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="is_active" value="{{ $fiat->is_active ? 1 : 0 }}">
                                        <div>
                                            <x-input-label value="Name" />
                                            <x-text-input name="name" value="{{ $fiat->name }}" required class="mt-1 w-full" />
                                        </div>
                                        <div>
                                            <x-input-label value="Symbol" />
                                            <x-text-input name="symbol" value="{{ $fiat->symbol }}" required class="mt-1 w-full" />
                                        </div>
                                        <div>
                                            <x-input-label value="Exchange Rate to USD" />
                                            <x-text-input name="exchange_rate_to_usd" type="number" step="0.00000001" value="{{ $fiat->exchange_rate_to_usd }}" required class="mt-1 w-full" />
                                        </div>
                                        <div>
                                            <x-input-label value="Replace Flag / Logo" />
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
