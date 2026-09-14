<x-app-layout>
    <x-slot name="title">Referral Program</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div>
            <h2 class="text-xl font-bold text-charcoal-900 dark:text-white">Referral Program</h2>
            <p class="text-sm text-charcoal-500 dark:text-charcoal-400">
                Earn {{ rtrim(rtrim(number_format($commissionRate, 2), '0'), '.') }}% commission every time someone you invite
                completes a buy or sell.
                @if ($signupBonusEnabled)
                    Plus a ${{ number_format($signupBonusAmount, 2) }} bonus on their first trade.
                @endif
            </p>
        </div>

        {{-- Share card --}}
        <div class="rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 text-white p-6 shadow-lg" x-data="{ copied: false, copy(text) { navigator.clipboard.writeText(text); this.copied = true; setTimeout(() => this.copied = false, 2000); } }">
            <p class="text-sm text-brand-100">Your referral code</p>
            <p class="text-3xl font-extrabold tracking-wide mt-1">{{ $referralCode }}</p>
            <p class="text-xs text-brand-100 mt-3">Your referral link</p>
            <div class="mt-1 flex flex-col sm:flex-row gap-2">
                <input type="text" readonly value="{{ $referralLink }}" class="flex-1 rounded-lg bg-white/10 border-white/20 text-white text-sm px-3 py-2 focus:ring-white/40 focus:border-white/40" onclick="this.select()">
                <button type="button" @click="copy('{{ $referralLink }}')" class="px-4 py-2 rounded-lg bg-white text-brand-700 text-sm font-semibold whitespace-nowrap">
                    <span x-show="!copied">Copy Link</span>
                    <span x-show="copied" style="display:none">Copied!</span>
                </button>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-4 shadow-sm">
                <p class="text-xs text-charcoal-400">People Referred</p>
                <p class="text-xl font-extrabold text-charcoal-900 dark:text-white mt-1">{{ $totalReferred }}</p>
            </div>
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-4 shadow-sm">
                <p class="text-xs text-charcoal-400">Total Earned</p>
                <p class="text-xl font-extrabold text-charcoal-900 dark:text-white mt-1">${{ number_format($totalEarned, 2) }}</p>
            </div>
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-4 shadow-sm">
                <p class="text-xs text-charcoal-400">Available Balance</p>
                <p class="text-xl font-extrabold text-green-600">${{ number_format($availableBalance, 2) }}</p>
            </div>
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-4 shadow-sm">
                <p class="text-xs text-charcoal-400">Min. Withdrawal</p>
                <p class="text-xl font-extrabold text-charcoal-900 dark:text-white mt-1">${{ number_format($minWithdrawal, 2) }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            {{-- Withdrawal request --}}
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                <h3 class="font-semibold text-charcoal-900 dark:text-white mb-4">Request a Withdrawal</h3>
                @if ($bankAccounts->isEmpty())
                    <p class="text-sm text-amber-600">You need at least one bank account before withdrawing. <a href="{{ route('bank-accounts.index') }}" class="underline font-semibold">Add a bank account</a>.</p>
                @elseif ($availableBalance < $minWithdrawal)
                    <p class="text-sm text-charcoal-400">You need at least ${{ number_format($minWithdrawal, 2) }} available to request a withdrawal. Keep referring to grow your balance!</p>
                @else
                    <form method="POST" action="{{ route('referrals.withdraw') }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label value="Amount (USD)" />
                            <x-text-input name="amount" type="number" step="0.01" min="{{ $minWithdrawal }}" max="{{ $availableBalance }}" value="{{ number_format($availableBalance, 2, '.', '') }}" class="mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label value="Payout Bank Account" />
                            <select name="bank_account_id" required class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                                @foreach ($bankAccounts as $account)
                                    <option value="{{ $account->id }}" @selected($account->is_default)>{{ $account->bank_name }} &middot; {{ $account->account_name }} ({{ $account->maskedAccountNumber() }})</option>
                                @endforeach
                            </select>
                        </div>
                        <x-primary-button class="w-full justify-center py-2.5">Request Withdrawal</x-primary-button>
                    </form>
                @endif

                @if ($withdrawals->isNotEmpty())
                    <div class="mt-6 pt-4 border-t border-charcoal-100 dark:border-charcoal-800">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-charcoal-400 mb-3">Your Withdrawal Requests</h4>
                        <div class="space-y-2">
                            @foreach ($withdrawals as $withdrawal)
                                <div class="flex items-center justify-between text-sm rounded-lg bg-charcoal-50 dark:bg-charcoal-800 px-3 py-2">
                                    <div>
                                        <p class="font-semibold">${{ number_format($withdrawal->amount, 2) }}</p>
                                        <p class="text-xs text-charcoal-400">{{ $withdrawal->created_at->diffForHumans() }} &middot; {{ $withdrawal->reference }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span @class([
                                            'text-xs font-medium px-2 py-0.5 rounded-full capitalize',
                                            'bg-amber-100 text-amber-700' => $withdrawal->status === 'pending',
                                            'bg-blue-100 text-blue-700' => $withdrawal->status === 'approved',
                                            'bg-green-100 text-green-700' => $withdrawal->status === 'paid',
                                            'bg-red-100 text-red-700' => $withdrawal->status === 'rejected',
                                        ])>{{ $withdrawal->status }}</span>
                                        @if ($withdrawal->status === 'pending')
                                            <form method="POST" action="{{ route('referrals.withdraw.cancel', $withdrawal) }}" onsubmit="return confirm('Cancel this withdrawal request?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Cancel</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                @if ($withdrawal->status === 'rejected' && $withdrawal->admin_note)
                                    <p class="text-xs text-red-500 px-3">Reason: {{ $withdrawal->admin_note }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Referred users --}}
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
                <div class="px-6 py-4 border-b border-charcoal-100 dark:border-charcoal-800">
                    <h3 class="font-semibold text-charcoal-900 dark:text-white">People You've Referred</h3>
                </div>
                <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800 max-h-80 overflow-y-auto">
                    @forelse ($referredUsers as $referredUser)
                        <div class="flex items-center justify-between px-6 py-3 text-sm">
                            <div>
                                <p class="font-semibold">{{ $referredUser->name }}</p>
                                <p class="text-xs text-charcoal-400">&#64;{{ $referredUser->username }}</p>
                            </div>
                            <p class="text-xs text-charcoal-400">Joined {{ $referredUser->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-sm text-charcoal-400 text-center">No one yet — share your link to start earning!</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Commission history --}}
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
            <div class="px-6 py-4 border-b border-charcoal-100 dark:border-charcoal-800">
                <h3 class="font-semibold text-charcoal-900 dark:text-white">Commission History</h3>
            </div>
            <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                @forelse ($commissions as $commission)
                    <div class="flex items-center justify-between px-6 py-3 text-sm">
                        <div>
                            <p class="font-semibold {{ $commission->is_reversed ? 'line-through text-charcoal-400' : '' }}">{{ $commission->typeLabel() }}</p>
                            <p class="text-xs text-charcoal-400">
                                {{ $commission->description ?? ($commission->referredUser?->username ? '@'.$commission->referredUser->username : '') }}
                                &middot; {{ $commission->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <p class="font-semibold {{ $commission->is_reversed ? 'line-through text-charcoal-400' : 'text-green-600' }}">+${{ number_format($commission->amount, 2) }}</p>
                    </div>
                @empty
                    <p class="px-6 py-8 text-sm text-charcoal-400 text-center">No commissions yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
