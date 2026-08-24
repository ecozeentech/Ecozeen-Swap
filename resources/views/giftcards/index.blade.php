<x-app-layout>
    <x-slot name="title">Gift Cards</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{
            products: {{ $products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'currency' => $p->currency, 'min' => (float) $p->min_amount, 'max' => (float) $p->max_amount])->toJson() }},
            selectedProduct: null,
            get selected() { return this.products.find(p => p.id === this.selectedProduct) ?? null },
        }">
        <div class="lg:col-span-2 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-charcoal-900 dark:text-white mb-1">Sell a Gift Card</h2>
            <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-4">Select a brand below, then submit your card details for review.</p>

            @if ($products->isEmpty())
                <p class="text-sm text-charcoal-400 py-8 text-center">No gift cards are available for your region right now. Please check back soon.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
                    @foreach ($products as $product)
                        <button
                            type="button"
                            @click="selectedProduct = {{ $product->id }}"
                            class="flex flex-col items-center gap-2 rounded-xl border-2 px-3 py-4 transition"
                            :class="selectedProduct === {{ $product->id }} ? 'border-brand-500 bg-brand-50 dark:bg-charcoal-800' : 'border-charcoal-100 dark:border-charcoal-800 hover:border-brand-300'"
                        >
                            <img src="{{ $product->logoUrl() }}" alt="{{ $product->name }}" class="h-10 w-10 rounded-lg object-cover">
                            <span class="text-xs font-semibold text-center">{{ $product->name }}</span>
                            <span class="text-[10px] text-charcoal-400">{{ $product->rate_override ?? $defaultBuybackRate }}% rate</span>
                        </button>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('giftcards.store') }}" enctype="multipart/form-data" class="space-y-4" x-show="selectedProduct" x-transition style="display:none">
                    @csrf
                    <input type="hidden" name="gift_card_product_id" :value="selectedProduct">

                    <div class="rounded-lg bg-brand-50 dark:bg-charcoal-800 px-4 py-2 text-xs text-charcoal-500 dark:text-charcoal-400" x-show="selected">
                        <span x-text="selected?.name"></span> &middot; accepted amount: <span x-text="selected?.min"></span>&ndash;<span x-text="selected?.max"></span> <span x-text="selected?.currency"></span>
                    </div>

                    <div>
                        <x-input-label value="Face Value" />
                        <x-text-input name="face_value" type="number" step="0.01" min="1" required class="mt-1 w-full" />
                    </div>

                    <div>
                        <x-input-label value="Card Number / Code" />
                        <x-text-input name="card_number" type="text" required class="mt-1 w-full" />
                    </div>

                    <div>
                        <x-input-label value="PIN (if applicable)" />
                        <x-text-input name="pin" type="text" class="mt-1 w-full" />
                    </div>

                    <div>
                        <x-input-label value="Card Photo (optional)" />
                        <input type="file" name="card_image" accept="image/*" class="mt-1 w-full text-sm">
                    </div>

                    <x-primary-button class="w-full justify-center py-3">Submit for Review</x-primary-button>
                </form>
            @endif
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
            <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800">
                <h3 class="font-semibold text-charcoal-900 dark:text-white">Your Submissions</h3>
            </div>
            <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800 max-h-[32rem] overflow-y-auto">
                @forelse ($giftCards as $card)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <img src="{{ $card->product?->logoUrl() ?? asset('images/giftcard-placeholder.svg') }}" class="h-8 w-8 rounded-lg object-cover" alt="">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-sm">{{ $card->card_type }}</span>
                                <span @class([
                                    'text-xs font-medium px-2 py-0.5 rounded-full',
                                    'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' => $card->status === 'paid',
                                    'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' => in_array($card->status, ['pending', 'reviewing']),
                                    'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $card->status === 'rejected',
                                ])>{{ ucfirst($card->status) }}</span>
                            </div>
                            <p class="text-xs text-charcoal-400 mt-1">{{ number_format($card->face_value, 2) }} {{ $card->face_value_currency }} &middot; est. payout {{ number_format($card->selling_price, 2) }} {{ $card->payout_currency }}</p>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-charcoal-400 text-center">No gift cards submitted yet.</p>
                @endforelse
            </div>
            <div class="px-5 py-3 border-t border-charcoal-100 dark:border-charcoal-800">
                {{ $giftCards->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
