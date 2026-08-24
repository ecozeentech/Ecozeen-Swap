<x-admin-layout>
    <x-slot name="title">Fiat Currencies</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <h3 class="font-semibold mb-4">Add Fiat Currency</h3>
            <form method="POST" action="{{ route('admin.fiat.store') }}" class="space-y-3">
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
                <x-primary-button class="w-full justify-center py-2.5">Add Currency</x-primary-button>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-charcoal-400 text-xs uppercase">
                    <tr><th class="px-5 py-3">Currency</th><th class="px-5 py-3">Rate to USD</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                    @foreach ($currencies as $fiat)
                        <tr>
                            <td class="px-5 py-3 font-semibold">{{ $fiat->name }} ({{ $fiat->code }})</td>
                            <td class="px-5 py-3 text-charcoal-400">{{ $fiat->exchange_rate_to_usd }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $fiat->is_active ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600' }}">{{ $fiat->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <form method="POST" action="{{ route('admin.fiat.update', $fiat) }}" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="name" value="{{ $fiat->name }}">
                                    <input type="hidden" name="symbol" value="{{ $fiat->symbol }}">
                                    <input type="hidden" name="exchange_rate_to_usd" value="{{ $fiat->exchange_rate_to_usd }}">
                                    <input type="hidden" name="is_active" value="{{ $fiat->is_active ? 0 : 1 }}">
                                    <button type="submit" class="text-xs font-semibold text-brand-600 hover:underline">{{ $fiat->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
