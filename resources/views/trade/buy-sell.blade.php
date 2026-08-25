<x-app-layout>
    <x-slot name="title">Trading Engine</x-slot>

    @php $initialTab = $activeTab === 'buy' && ! $buyEnabled ? 'sell' : ($activeTab === 'sell' && ! $sellEnabled ? 'buy' : $activeTab); @endphp
    <div class="max-w-5xl mx-auto" x-data="{ tab: '{{ $initialTab }}' }">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-charcoal-900 dark:text-white">Trading Engine</h1>
            <p class="text-sm text-charcoal-500 dark:text-charcoal-400">Execute precise, institutional-grade orders instantly.</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <div class="inline-flex rounded-xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-1 shadow-sm">
                    @if ($buyEnabled)
                        <button type="button" @click="tab = 'buy'" class="px-6 py-2 rounded-lg text-sm font-semibold transition" :class="tab === 'buy' ? 'bg-brand-500 text-white shadow-sm' : 'text-charcoal-500 dark:text-charcoal-400'">Buy</button>
                    @else
                        <span class="px-6 py-2 rounded-lg text-sm font-semibold text-charcoal-300 dark:text-charcoal-600 cursor-not-allowed" title="Buy is temporarily disabled">Buy</span>
                    @endif
                    @if ($sellEnabled)
                        <button type="button" @click="tab = 'sell'" class="px-6 py-2 rounded-lg text-sm font-semibold transition" :class="tab === 'sell' ? 'bg-brand-500 text-white shadow-sm' : 'text-charcoal-500 dark:text-charcoal-400'">Sell</button>
                    @else
                        <span class="px-6 py-2 rounded-lg text-sm font-semibold text-charcoal-300 dark:text-charcoal-600 cursor-not-allowed" title="Sell is temporarily disabled">Sell</span>
                    @endif
                    <a href="{{ route('swap.index') }}" class="px-6 py-2 rounded-lg text-sm font-semibold text-charcoal-500 dark:text-charcoal-400 hover:text-brand-600">Swap</a>
                </div>

                @if (auth()->user()->kyc_status !== 'verified')
                    <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3 text-sm text-amber-800 dark:text-amber-300">
                        Complete KYC to increase your limits. Your current daily limit is <strong>${{ number_format(auth()->user()->daily_trade_limit, 2) }}</strong> USD equivalent &mdash; you can keep trading while your documents are reviewed.
                        <a href="{{ route('security.kyc') }}" class="font-semibold underline">Upload documents</a>
                    </div>
                @endif

                @if ($buyEnabled)
                    <div x-show="tab === 'buy'" class="space-y-4">
                        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300 flex gap-2">
                            <svg class="h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <span><strong>Important Payment Instruction:</strong> Do not include references to "crypto", any coin name, or "Ecozeen Swap" in your bank transfer memo/description. Use only the provided reference ID.</span>
                        </div>

                        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                            <form method="POST" action="{{ route('buy.store') }}" class="space-y-4">
                                @csrf
                                <livewire:buy-calculator />

                                <div>
                                    <x-input-label value="Payment Method" />
                                    <div class="mt-2 space-y-2">
                                        @foreach ($gateways as $gateway)
                                            <label class="flex items-center gap-3 rounded-lg border border-charcoal-200 dark:border-charcoal-700 px-4 py-3 cursor-pointer hover:border-brand-400">
                                                <input type="radio" name="payment_method" value="{{ $gateway->slug }}" required class="text-brand-600 focus:ring-brand-500">
                                                <span class="text-sm font-medium">{{ $gateway->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg shadow-sm">Execute Buy Order</button>
                            </form>
                        </div>
                    </div>
                @endif

                @if ($sellEnabled)
                    <div x-show="tab === 'sell'" style="display:none" class="space-y-4">
                        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                            <form method="POST" action="{{ route('sell.store') }}" class="space-y-4">
                                @csrf
                                <livewire:sell-calculator />

                                <div>
                                    <x-input-label value="Settlement Bank Account" />
                                    @if ($bankAccounts->isEmpty())
                                        <p class="mt-1 text-sm text-amber-600">You need at least one bank account before selling. <a href="{{ route('bank-accounts.index') }}" class="underline font-semibold">Add a bank account</a>.</p>
                                    @else
                                        <select name="bank_account_id" required class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                                            @foreach ($bankAccounts as $account)
                                                <option value="{{ $account->id }}" @selected($account->is_default)>{{ $account->bank_name }} &middot; {{ $account->account_name }} ({{ $account->maskedAccountNumber() }})</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>

                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg shadow-sm" @disabled($bankAccounts->isEmpty())>Execute Sell Order</button>
                            </form>
                        </div>
                    </div>
                @endif

                @if (! $buyEnabled || ! $sellEnabled)
                    <div class="rounded-2xl border border-dashed border-charcoal-200 dark:border-charcoal-700 p-6 text-center text-sm text-charcoal-400"
                         x-show="(tab === 'buy' && {{ $buyEnabled ? 'false' : 'true' }}) || (tab === 'sell' && {{ $sellEnabled ? 'false' : 'true' }})" style="display:none">
                        This feature is temporarily unavailable. Please check back shortly.
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-5 shadow-sm">
                    <h3 class="font-semibold text-charcoal-900 dark:text-white flex items-center gap-2 mb-4">
                        <svg class="h-5 w-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" /></svg>
                        Transaction Summary
                    </h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-charcoal-400">Rate Source</dt><dd class="font-semibold">Ecozeen Swap (24h rate)</dd></div>
                        <div class="flex justify-between"><dt class="text-charcoal-400">Network Fee</dt><dd class="font-semibold text-green-600">Free</dd></div>
                        <div class="flex justify-between"><dt class="text-charcoal-400">Settlement</dt><dd class="font-semibold" x-text="tab === 'buy' ? 'Instant on confirmation' : 'After crypto receipt is confirmed'"></dd></div>
                    </dl>
                </div>

                <div x-show="tab === 'sell'" style="display:none" class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-5 shadow-sm">
                    <h3 class="font-semibold text-charcoal-900 dark:text-white flex items-center gap-2 mb-3">
                        <svg class="h-5 w-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 10h16M4 10V6l8-4 8 4v4M6 10v11M10 10v11M14 10v11M18 10v11" /></svg>
                        Settlement Account
                    </h3>
                    <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-3">Your sell payout will be sent to your selected bank account once we confirm receipt of your crypto.</p>
                    @php $defaultAccount = $bankAccounts->firstWhere('is_default', true) ?? $bankAccounts->first(); @endphp
                    @if ($defaultAccount)
                        <div class="rounded-xl bg-charcoal-50 dark:bg-charcoal-800 p-3 text-sm flex items-center justify-between">
                            <div>
                                <p class="font-semibold">{{ $defaultAccount->bank_name }}</p>
                                <p class="text-xs text-charcoal-400">{{ $defaultAccount->maskedAccountNumber() }}</p>
                            </div>
                            <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </div>
                    @endif
                    <a href="{{ route('bank-accounts.index') }}" class="mt-3 inline-block text-xs font-semibold text-brand-600 hover:underline">Manage bank accounts</a>
                </div>

                <div class="rounded-2xl bg-charcoal-50 dark:bg-charcoal-800 p-4 text-xs text-charcoal-500 dark:text-charcoal-400 flex items-center gap-2">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 10-8 0v3h8z" /></svg>
                    Secure institutional-grade settlement
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
