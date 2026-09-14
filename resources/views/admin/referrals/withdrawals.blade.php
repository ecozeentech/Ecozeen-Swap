<x-admin-layout>
    <x-slot name="title">Referral Withdrawal Requests</x-slot>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <select name="status" onchange="this.form.submit()" class="rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
            <option value="">Pending &amp; Approved</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
            <option value="paid" @selected(request('status') === 'paid')>Paid</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
        </select>
    </form>

    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-charcoal-400 text-xs uppercase bg-charcoal-50 dark:bg-charcoal-800/50">
                <tr>
                    <th class="px-5 py-3">Reference</th>
                    <th class="px-5 py-3">User</th>
                    <th class="px-5 py-3">Amount</th>
                    <th class="px-5 py-3">Payout Account</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Requested</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                @forelse ($withdrawals as $withdrawal)
                    <tr>
                        <td class="px-5 py-3 font-mono text-xs">{{ $withdrawal->reference }}</td>
                        <td class="px-5 py-3">
                            <p class="font-semibold">{{ $withdrawal->user->name }}</p>
                            <p class="text-xs text-charcoal-400">&#64;{{ $withdrawal->user->username }}</p>
                        </td>
                        <td class="px-5 py-3 font-semibold">${{ number_format($withdrawal->amount, 2) }}</td>
                        <td class="px-5 py-3 text-charcoal-400">
                            @if ($withdrawal->bankAccount)
                                {{ $withdrawal->bankAccount->bank_name }} &middot; {{ $withdrawal->bankAccount->maskedAccountNumber() }}
                            @else
                                <span class="text-red-500">Bank account deleted</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span @class([
                                'text-xs font-medium px-2 py-0.5 rounded-full capitalize',
                                'bg-amber-100 text-amber-700' => $withdrawal->status === 'pending',
                                'bg-blue-100 text-blue-700' => $withdrawal->status === 'approved',
                                'bg-green-100 text-green-700' => $withdrawal->status === 'paid',
                                'bg-red-100 text-red-700' => $withdrawal->status === 'rejected',
                            ])>{{ $withdrawal->status }}</span>
                        </td>
                        <td class="px-5 py-3 text-charcoal-400">{{ $withdrawal->created_at->diffForHumans() }}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            @if ($withdrawal->status === 'pending')
                                <form method="POST" action="{{ route('admin.referrals.withdrawals.approve', $withdrawal) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-blue-600 hover:underline mr-3">Approve</button>
                                </form>
                            @endif
                            @if (in_array($withdrawal->status, ['pending', 'approved']))
                                <form method="POST" action="{{ route('admin.referrals.withdrawals.paid', $withdrawal) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-green-600 hover:underline mr-3">Mark Paid</button>
                                </form>
                                <form method="POST" action="{{ route('admin.referrals.withdrawals.reject', $withdrawal) }}" onsubmit="return promptReject(this)" class="inline">
                                    @csrf
                                    <input type="hidden" name="reason" value="">
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Reject</button>
                                </form>
                            @endif
                            @if ($withdrawal->status === 'rejected' && $withdrawal->admin_note)
                                <span class="text-xs text-charcoal-400">{{ $withdrawal->admin_note }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-charcoal-400">No withdrawal requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $withdrawals->links() }}</div>

    <script>
        function promptReject(form) {
            const reason = prompt('Rejection reason:');
            if (!reason) return false;
            form.querySelector('input[name="reason"]').value = reason;
            return true;
        }
    </script>
</x-admin-layout>
