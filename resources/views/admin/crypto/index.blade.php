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

        <div class="lg:col-span-2 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-charcoal-400 text-xs uppercase">
                    <tr><th class="px-5 py-3">Asset</th><th class="px-5 py-3">Network</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                    @foreach ($assets as $asset)
                        <tr>
                            <td class="px-5 py-3 font-semibold">{{ $asset->name }} ({{ $asset->symbol }})</td>
                            <td class="px-5 py-3 text-charcoal-400">{{ $asset->network }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $asset->is_active ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600' }}">{{ $asset->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <form method="POST" action="{{ route('admin.crypto.update', $asset) }}" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="name" value="{{ $asset->name }}">
                                    <input type="hidden" name="network" value="{{ $asset->network }}">
                                    <input type="hidden" name="decimal_places" value="{{ $asset->decimal_places }}">
                                    <input type="hidden" name="is_active" value="{{ $asset->is_active ? 0 : 1 }}">
                                    <button type="submit" class="text-xs font-semibold text-brand-600 hover:underline">{{ $asset->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
