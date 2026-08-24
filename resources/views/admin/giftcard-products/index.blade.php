<x-admin-layout>
    <x-slot name="title">Gift Card Catalog</x-slot>

    <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-6">
        Manage the gift card brands users can sell to Ecozeen Swap. Leave "Available Countries" empty to make a
        card available worldwide, or select specific countries to restrict it.
    </p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <h3 class="font-semibold mb-4">Add Gift Card</h3>
            <form method="POST" action="{{ route('admin.giftcard-products.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <x-input-label value="Name" />
                    <x-text-input name="name" required class="mt-1 w-full" placeholder="Amazon Gift Card" />
                </div>
                <div>
                    <x-input-label value="Logo" />
                    <input type="file" name="logo" accept="image/*" class="mt-1 w-full text-sm">
                </div>
                <div>
                    <x-input-label value="Description" />
                    <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label value="Currency" />
                        <x-text-input name="currency" value="USD" required class="mt-1 w-full" />
                    </div>
                    <div>
                        <x-input-label value="Buyback % (optional)" />
                        <x-text-input name="rate_override" type="number" step="0.01" class="mt-1 w-full" placeholder="Uses global default" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label value="Min Amount" />
                        <x-text-input name="min_amount" type="number" step="0.01" value="1" required class="mt-1 w-full" />
                    </div>
                    <div>
                        <x-input-label value="Max Amount" />
                        <x-text-input name="max_amount" type="number" step="0.01" value="5000" required class="mt-1 w-full" />
                    </div>
                </div>
                <div>
                    <x-input-label value="Available Countries (leave empty for worldwide)" />
                    <select name="countries[]" multiple size="6" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500 text-sm">
                        @foreach ($countries as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-primary-button class="w-full justify-center py-2.5">Add Gift Card</x-primary-button>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm divide-y divide-charcoal-100 dark:divide-charcoal-800">
            @forelse ($products as $product)
                <div x-data="{ editing: false }" class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ $product->logoUrl() }}" class="h-10 w-10 rounded-lg object-cover bg-charcoal-100" alt="">
                            <div>
                                <p class="font-semibold">{{ $product->name }}</p>
                                <p class="text-xs text-charcoal-400">
                                    {{ $product->currency }} {{ number_format($product->min_amount, 2) }}&ndash;{{ number_format($product->max_amount, 2) }}
                                    &middot; {{ $product->rate_override ? $product->rate_override.'% override' : 'Default rate' }}
                                    &middot; {{ $product->isAvailableEverywhere() ? 'Worldwide' : count($product->countries).' countries' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                            <button type="button" @click="editing = !editing" class="text-xs font-semibold text-brand-600 hover:underline">Edit</button>
                            <form method="POST" action="{{ route('admin.giftcard-products.toggle', $product) }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-amber-600 hover:underline">{{ $product->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.giftcard-products.destroy', $product) }}" onsubmit="return confirm('Delete {{ $product->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>

                    <div x-show="editing" x-transition style="display:none" class="mt-4 rounded-lg bg-charcoal-50 dark:bg-charcoal-800/40 p-4">
                        <form method="POST" action="{{ route('admin.giftcard-products.update', $product) }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @csrf @method('PUT')
                            <input type="hidden" name="is_active" value="{{ $product->is_active ? 1 : 0 }}">
                            <div class="sm:col-span-2">
                                <x-input-label value="Name" />
                                <x-text-input name="name" value="{{ $product->name }}" required class="mt-1 w-full" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label value="Description" />
                                <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white">{{ $product->description }}</textarea>
                            </div>
                            <div>
                                <x-input-label value="Currency" />
                                <x-text-input name="currency" value="{{ $product->currency }}" required class="mt-1 w-full" />
                            </div>
                            <div>
                                <x-input-label value="Buyback % override" />
                                <x-text-input name="rate_override" type="number" step="0.01" value="{{ $product->rate_override }}" class="mt-1 w-full" />
                            </div>
                            <div>
                                <x-input-label value="Min Amount" />
                                <x-text-input name="min_amount" type="number" step="0.01" value="{{ $product->min_amount }}" required class="mt-1 w-full" />
                            </div>
                            <div>
                                <x-input-label value="Max Amount" />
                                <x-text-input name="max_amount" type="number" step="0.01" value="{{ $product->max_amount }}" required class="mt-1 w-full" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label value="Available Countries (leave empty for worldwide)" />
                                <select name="countries[]" multiple size="6" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white text-sm">
                                    @foreach ($countries as $code => $name)
                                        <option value="{{ $code }}" @selected($product->countries && in_array($code, $product->countries))>{{ $name }}</option>
                                    @endforeach
                                </select>
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
                </div>
            @empty
                <p class="p-6 text-sm text-charcoal-400 text-center">No gift cards in the catalog yet. Add one to get started.</p>
            @endforelse
        </div>
    </div>
</x-admin-layout>
