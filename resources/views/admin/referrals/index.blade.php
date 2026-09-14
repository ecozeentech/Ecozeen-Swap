<x-admin-layout>
    <x-slot name="title">Referral Program</x-slot>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-4 shadow-sm">
            <p class="text-xs text-charcoal-400">Referrers</p>
            <p class="text-xl font-extrabold mt-1">{{ $stats['total_referrers'] }}</p>
        </div>
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-4 shadow-sm">
            <p class="text-xs text-charcoal-400">Referred Users</p>
            <p class="text-xl font-extrabold mt-1">{{ $stats['total_referred_users'] }}</p>
        </div>
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-4 shadow-sm">
            <p class="text-xs text-charcoal-400">Total Commissions</p>
            <p class="text-xl font-extrabold mt-1">${{ number_format($stats['total_paid_commissions'], 2) }}</p>
        </div>
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-4 shadow-sm">
            <p class="text-xs text-charcoal-400">Pending Withdrawals</p>
            <p class="text-xl font-extrabold mt-1">{{ $stats['pending_withdrawals'] }}</p>
        </div>
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-4 shadow-sm">
            <p class="text-xs text-charcoal-400">Pending Amount</p>
            <p class="text-xl font-extrabold mt-1">${{ number_format($stats['pending_withdrawal_amount'], 2) }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <h3 class="font-semibold mb-1">Program Settings</h3>
            <p class="text-xs text-charcoal-400 mb-4">
                Turn the whole program on/off from <a href="{{ route('admin.features.index') }}" class="text-brand-600 hover:underline">Feature Toggles</a>.
            </p>
            <form method="POST" action="{{ route('admin.referrals.settings') }}" x-data="{ bonusEnabled: {{ $settings->get('referral_signup_bonus_enabled')?->value ? 'true' : 'false' }} }" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <x-input-label value="Commission Rate (% of trade volume)" />
                    <x-text-input name="referral_commission_rate" type="number" step="0.01" min="0" max="100" value="{{ $settings->get('referral_commission_rate')?->value ?? \App\Services\ReferralService::DEFAULT_COMMISSION_RATE }}" class="mt-1 w-full" required />
                    <p class="text-xs text-charcoal-400 mt-1">Paid to the referrer on every completed buy/sell by someone they referred.</p>
                </div>
                <div>
                    <x-input-label value="Minimum Withdrawal Balance (USD)" />
                    <x-text-input name="referral_min_withdrawal" type="number" step="0.01" min="0" value="{{ $settings->get('referral_min_withdrawal')?->value ?? \App\Services\ReferralService::DEFAULT_MIN_WITHDRAWAL }}" class="mt-1 w-full" required />
                    <p class="text-xs text-charcoal-400 mt-1">Users must accumulate at least this much before they can request a withdrawal.</p>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="referral_signup_bonus_enabled" value="1" x-model="bonusEnabled" @checked($settings->get('referral_signup_bonus_enabled')?->value) class="rounded border-charcoal-300 text-brand-600 focus:ring-brand-500">
                    Pay a one-off signup bonus on the referred user's first completed trade
                </label>
                <div x-show="bonusEnabled" style="display:none">
                    <x-input-label value="Signup Bonus Amount (USD)" />
                    <x-text-input name="referral_signup_bonus_amount" type="number" step="0.01" min="0" value="{{ $settings->get('referral_signup_bonus_amount')?->value ?? \App\Services\ReferralService::DEFAULT_SIGNUP_BONUS }}" class="mt-1 w-full" />
                </div>
                <x-primary-button class="w-full justify-center py-2.5">Save Settings</x-primary-button>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
            <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800"><h3 class="font-semibold">Top Referrers</h3></div>
            <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800 max-h-96 overflow-y-auto">
                @forelse ($topReferrers as $row)
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <div>
                            <p class="font-semibold">{{ $row['user']->name }}</p>
                            <p class="text-xs text-charcoal-400">&#64;{{ $row['user']->username }} &middot; {{ $row['user']->referred_users_count }} referred</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">${{ number_format($row['earned'], 2) }} earned</p>
                            <p class="text-xs text-charcoal-400">${{ number_format($row['available'], 2) }} available</p>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-sm text-charcoal-400 text-center">No referrals yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm overflow-x-auto">
        <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800"><h3 class="font-semibold">Commission Ledger</h3></div>
        <table class="w-full text-sm">
            <thead class="text-left text-charcoal-400 text-xs uppercase bg-charcoal-50 dark:bg-charcoal-800/50">
                <tr>
                    <th class="px-5 py-3">Referrer</th>
                    <th class="px-5 py-3">Referred User</th>
                    <th class="px-5 py-3">Type</th>
                    <th class="px-5 py-3">Amount</th>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                @forelse ($commissions as $commission)
                    <tr class="{{ $commission->is_reversed ? 'opacity-50' : '' }}">
                        <td class="px-5 py-3 font-semibold">&#64;{{ $commission->referrer->username ?? 'N/A' }}</td>
                        <td class="px-5 py-3 text-charcoal-400">{{ $commission->referredUser?->username ? '@'.$commission->referredUser->username : '—' }}</td>
                        <td class="px-5 py-3">{{ $commission->typeLabel() }}</td>
                        <td class="px-5 py-3 font-semibold">${{ number_format($commission->amount, 2) }}</td>
                        <td class="px-5 py-3 text-charcoal-400">{{ $commission->created_at->format('M d, H:i') }}</td>
                        <td class="px-5 py-3 text-right">
                            @if ($commission->is_reversed)
                                <span class="text-xs text-charcoal-400">Reversed</span>
                            @else
                                <form method="POST" action="{{ route('admin.referrals.reverse-commission', $commission) }}" onsubmit="return confirm('Reverse this commission? It will no longer count toward the referrer&#39;s balance.')">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Reverse</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-charcoal-400">No commissions recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
