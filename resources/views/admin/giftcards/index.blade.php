<x-admin-layout>
    <x-slot name="title">Gift Card Verification</x-slot>

    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-charcoal-400 text-xs uppercase">
                <tr>
                    <th class="px-5 py-3">User</th>
                    <th class="px-5 py-3">Card</th>
                    <th class="px-5 py-3">Face Value</th>
                    <th class="px-5 py-3">Payout</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                @foreach ($giftCards as $card)
                    <tr>
                        <td class="px-5 py-3">{{ $card->user->username ?? 'N/A' }}</td>
                        <td class="px-5 py-3">{{ $card->card_type }} &middot; {{ $card->maskedCardNumber() }}</td>
                        <td class="px-5 py-3">{{ number_format($card->face_value, 2) }} {{ $card->face_value_currency }}</td>
                        <td class="px-5 py-3">{{ number_format($card->selling_price, 2) }} {{ $card->payout_currency }}</td>
                        <td class="px-5 py-3">
                            <span @class([
                                'text-xs font-medium px-2 py-0.5 rounded-full capitalize',
                                'bg-green-100 text-green-700' => $card->status === 'paid',
                                'bg-amber-100 text-amber-700' => in_array($card->status, ['pending', 'reviewing']),
                                'bg-red-100 text-red-700' => $card->status === 'rejected',
                            ])>{{ $card->status }}</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if (in_array($card->status, ['pending', 'reviewing']))
                                <div class="flex gap-2 justify-end">
                                    <form method="POST" action="{{ route('admin.giftcards.approve', $card) }}"><input type="hidden">@csrf<x-primary-button class="!text-[10px] !px-3 !py-1.5">Approve</x-primary-button></form>
                                    <form method="POST" action="{{ route('admin.giftcards.reject', $card) }}" onsubmit="return promptReject(this)">@csrf<input type="hidden" name="reason" value=""><x-danger-button class="!text-[10px] !px-3 !py-1.5">Reject</x-danger-button></form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $giftCards->links() }}</div>

    <script>
        function promptReject(form) {
            const reason = prompt('Rejection reason:');
            if (!reason) return false;
            form.querySelector('input[name="reason"]').value = reason;
            return true;
        }
    </script>
</x-admin-layout>
