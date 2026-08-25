<x-admin-layout>
    <x-slot name="title">Manage Users</x-slot>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, username, email" class="rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500 flex-1 min-w-[200px]">
        <select name="kyc_status" class="rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
            <option value="">All KYC Statuses</option>
            @foreach (\App\Models\User::kycStatusOptions() as $status => $label)
                <option value="{{ $status }}" @selected(request('kyc_status') === $status)>{{ $label }}</option>
            @endforeach
        </select>
        <x-secondary-button type="submit">Filter</x-secondary-button>
    </form>

    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-charcoal-400 text-xs uppercase">
                <tr>
                    <th class="px-5 py-3">User</th>
                    <th class="px-5 py-3">KYC</th>
                    <th class="px-5 py-3">Daily Limit</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Joined</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-5 py-3">
                            <p class="font-semibold">{{ $user->name }}</p>
                            <p class="text-xs text-charcoal-400">{{ $user->username }} &middot; {{ $user->email }}</p>
                        </td>
                        <td class="px-5 py-3">{{ $user->kycLabel() }}</td>
                        <td class="px-5 py-3">${{ number_format($user->daily_trade_limit, 2) }}</td>
                        <td class="px-5 py-3">
                            @if ($user->is_suspended)
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-red-100 text-red-700">Suspended</span>
                            @else
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700">Active</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-charcoal-400">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-brand-600 font-semibold hover:underline">Manage</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-admin-layout>
