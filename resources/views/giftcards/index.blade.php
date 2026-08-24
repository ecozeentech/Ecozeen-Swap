<x-app-layout>
    <x-slot name="title">Gift Cards</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-charcoal-900 dark:text-white mb-1">Sell a Gift Card</h2>
            <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-4">Current buyback rate: <strong class="text-brand-600">{{ $buybackRate }}%</strong> of face value.</p>

            <form method="POST" action="{{ route('giftcards.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="card_type" value="Card Type" />
                    <select id="card_type" name="card_type" required class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                        @foreach ($cardTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="face_value" value="Face Value" />
                        <x-text-input id="face_value" name="face_value" type="number" step="0.01" min="1" required class="mt-1 w-full" />
                    </div>
                    <div>
                        <x-input-label for="face_value_currency" value="Currency" />
                        <select id="face_value_currency" name="face_value_currency" required class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                            <option value="USD">USD</option>
                            <option value="NGN">NGN</option>
                            <option value="GHS">GHS</option>
                        </select>
                    </div>
                </div>

                <div>
                    <x-input-label for="card_number" value="Card Number / Code" />
                    <x-text-input id="card_number" name="card_number" type="text" required class="mt-1 w-full" />
                </div>

                <div>
                    <x-input-label for="pin" value="PIN (if applicable)" />
                    <x-text-input id="pin" name="pin" type="text" class="mt-1 w-full" />
                </div>

                <div>
                    <x-input-label for="card_image" value="Card Photo (optional)" />
                    <input id="card_image" name="card_image" type="file" accept="image/*" class="mt-1 w-full text-sm text-charcoal-600 dark:text-charcoal-300">
                </div>

                <x-primary-button class="w-full justify-center py-3">Submit for Review</x-primary-button>
            </form>
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
            <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800">
                <h3 class="font-semibold text-charcoal-900 dark:text-white">Your Submissions</h3>
            </div>
            <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800 max-h-[32rem] overflow-y-auto">
                @forelse ($giftCards as $card)
                    <div class="px-5 py-3">
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
