@php
    $typeIcons = [
        'buy' => ['bg-green-100 text-green-600', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0 0l-5-5m5 5l5-5" />'],
        'sell' => ['bg-red-100 text-red-600', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 20V4m0 0l5 5m-5-5l-5 5" />'],
        'deposit' => ['bg-blue-100 text-blue-600', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />'],
        'withdrawal' => ['bg-amber-100 text-amber-600', '<path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />'],
        'swap' => ['bg-purple-100 text-purple-600', '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" />'],
        'giftcard' => ['bg-pink-100 text-pink-600', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 10-2 2h2zm0 0a2 2 0 102-2h-2zm-8 5h16M5 8h14a1 1 0 011 1v3H4V9a1 1 0 011-1zm-1 4h16v7a1 1 0 01-1 1H5a1 1 0 01-1-1v-7z" />'],
    ];
    [$iconClasses, $iconPath] = $typeIcons[$transaction->type] ?? ['bg-charcoal-100 text-charcoal-600', '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />'];
@endphp
<x-admin-layout>
    <x-slot name="title">Order {{ $transaction->reference }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <a href="{{ route('admin.transactions.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-charcoal-500 hover:text-brand-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Back to all orders
        </a>

        {{-- Header card --}}
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl flex items-center justify-center flex-shrink-0 {{ $iconClasses }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">{!! $iconPath !!}</svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-charcoal-900 dark:text-white capitalize">{{ $transaction->type }} Order</h2>
                        <p class="text-sm font-mono text-charcoal-400">{{ $transaction->reference }}</p>
                    </div>
                </div>
                <span @class([
                    'text-xs font-semibold px-3 py-1.5 rounded-full capitalize',
                    'bg-green-100 text-green-700' => $transaction->status === 'completed',
                    'bg-amber-100 text-amber-700' => in_array($transaction->status, ['pending', 'processing']),
                    'bg-red-100 text-red-700' => in_array($transaction->status, ['failed', 'cancelled']),
                ])>{{ $transaction->status }}</span>
            </div>

            <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 pt-6 border-t border-charcoal-100 dark:border-charcoal-800">
                <div>
                    <p class="text-xs text-charcoal-400">Amount</p>
                    <p class="font-bold text-charcoal-900 dark:text-white mt-0.5">{{ number_format($transaction->amount, 8) }} {{ $transaction->currency_code }}</p>
                </div>
                <div>
                    <p class="text-xs text-charcoal-400">≈ USD Value</p>
                    <p class="font-bold text-charcoal-900 dark:text-white mt-0.5">${{ number_format($transaction->usdEquivalent(), 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-charcoal-400">Placed</p>
                    <p class="font-semibold text-charcoal-700 dark:text-charcoal-300 mt-0.5">{{ $transaction->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-charcoal-400">Processed</p>
                    <p class="font-semibold text-charcoal-700 dark:text-charcoal-300 mt-0.5">{{ $transaction->processed_at?->format('M d, Y H:i') ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Customer card --}}
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-charcoal-400 uppercase tracking-wide mb-3">Customer</h3>
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-charcoal-900 dark:text-white">{{ $transaction->user->name ?? 'N/A' }}</p>
                    <p class="text-sm text-charcoal-400">&commat;{{ $transaction->user->username ?? 'n/a' }} &middot; {{ $transaction->user->email ?? '' }}</p>
                </div>
                @if ($transaction->user)
                    <a href="{{ route('admin.users.show', $transaction->user) }}" class="text-xs font-semibold text-brand-600 hover:underline whitespace-nowrap">View Profile →</a>
                @endif
            </div>
        </div>

        {{-- Wallet / address card --}}
        @php
            $destinationAddress = $transaction->metadata['deposit_address'] ?? ($transaction->metadata['destination'] ?? ($transaction->metadata['receiving_wallet_address'] ?? null));
            $bankDetails = $transaction->metadata['settlement_bank_name'] ?? ($transaction->metadata['bank_name'] ?? null);
        @endphp
        @if ($destinationAddress || $bankDetails)
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-charcoal-400 uppercase tracking-wide mb-3">
                    {{ $destinationAddress ? 'Destination Wallet' : 'Settlement Account' }}
                </h3>

                @if ($destinationAddress)
                    <div x-data="{ copied: false, copy() { navigator.clipboard.writeText('{{ $destinationAddress }}'); this.copied = true; setTimeout(() => this.copied = false, 1500); } }"
                         class="rounded-xl bg-charcoal-900 dark:bg-black p-4 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-9 w-9 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                                <svg class="h-4.5 w-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" /></svg>
                            </div>
                            <p class="font-mono text-sm text-white truncate" title="{{ $destinationAddress }}">{{ $destinationAddress }}</p>
                        </div>
                        <button type="button" @click="copy()" class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg bg-white/10 text-white hover:bg-white/20 transition">
                            <span x-show="!copied">Copy</span>
                            <span x-show="copied" style="display:none">Copied!</span>
                        </button>
                    </div>
                    @if (! empty($transaction->metadata['memo_tag']))
                        <p class="mt-2 text-xs text-charcoal-400">Memo/Tag: <span class="font-mono text-charcoal-600 dark:text-charcoal-300">{{ $transaction->metadata['memo_tag'] }}</span></p>
                    @endif
                    @if ($transaction->type === 'buy')
                        <p class="mt-2 text-xs text-charcoal-400">The user asked for this purchase to be sent to the address above instead of held in their Ecozeen Swap wallet. Send it manually from platform reserves once payment is confirmed, then mark this order paid.</p>
                    @endif
                @elseif ($bankDetails)
                    @php
                        $accountNumber = $transaction->metadata['account_number'] ?? ($transaction->metadata['settlement_account_number'] ?? null);
                        $accountName = $transaction->metadata['settlement_account_name'] ?? ($transaction->metadata['account_name'] ?? '');
                    @endphp
                    <div class="rounded-xl bg-charcoal-50 dark:bg-charcoal-800 p-4 flex items-center gap-3 mb-3">
                        <div class="h-9 w-9 rounded-lg bg-white dark:bg-charcoal-900 flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="h-4.5 w-4.5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 10h16M4 10V6l8-4 8 4v4M6 10v11M10 10v11M14 10v11M18 10v11" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-charcoal-900 dark:text-white">{{ $bankDetails }}</p>
                            <p class="text-xs text-charcoal-400">{{ $accountName }}</p>
                        </div>
                    </div>

                    @if ($accountNumber)
                        <div x-data="{ copied: false, copy() { navigator.clipboard.writeText('{{ $accountNumber }}'); this.copied = true; setTimeout(() => this.copied = false, 1500); } }"
                             class="rounded-xl bg-charcoal-900 dark:bg-black p-4 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="h-9 w-9 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="h-4.5 w-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[11px] text-white/50 uppercase tracking-wide">Account Number</p>
                                    <p class="font-mono text-sm text-white truncate" title="{{ $accountNumber }}">{{ $accountNumber }}</p>
                                </div>
                            </div>
                            <button type="button" @click="copy()" class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg bg-white/10 text-white hover:bg-white/20 transition">
                                <span x-show="!copied">Copy</span>
                                <span x-show="copied" style="display:none">Copied!</span>
                            </button>
                        </div>
                    @else
                        <p class="text-xs text-amber-500">This order was placed before full account numbers were recorded — only the last 4 digits (****{{ $transaction->metadata['account_number_last4'] ?? '????' }}) are available. Ask the user to confirm their account details directly.</p>
                    @endif
                @endif
            </div>
        @endif

        {{-- Proof of payment --}}
        @if ($transaction->depositProof)
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-charcoal-400 uppercase tracking-wide mb-3">Proof of Payment</h3>
                <a href="{{ route('admin.files.show', ['type' => 'deposit-proof', 'id' => $transaction->depositProof->id]) }}" target="_blank" class="block rounded-xl overflow-hidden border border-charcoal-100 dark:border-charcoal-800 group relative">
                    <img src="{{ route('admin.files.show', ['type' => 'deposit-proof', 'id' => $transaction->depositProof->id]) }}" alt="Proof of payment" class="w-full max-h-96 object-contain bg-charcoal-50 dark:bg-charcoal-800">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <span class="text-white text-sm font-semibold">Open full size →</span>
                    </div>
                </a>
                @if ($transaction->depositProof->note)
                    <p class="mt-3 text-sm text-charcoal-500 dark:text-charcoal-400"><strong>User note:</strong> {{ $transaction->depositProof->note }}</p>
                @endif
                <p class="mt-1 text-xs text-charcoal-400">Uploaded {{ $transaction->depositProof->created_at->diffForHumans() }}</p>
            </div>
        @endif

        {{-- Raw metadata (collapsed by default) --}}
        @if ($transaction->metadata)
            <div x-data="{ open: false }" class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 overflow-hidden shadow-sm">
                <button type="button" @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between text-left">
                    <h3 class="text-sm font-semibold text-charcoal-400 uppercase tracking-wide">Full Order Data</h3>
                    <svg class="h-4 w-4 text-charcoal-400 transition" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-show="open" x-transition style="display:none" class="px-6 pb-5 -mt-2">
                    <pre class="rounded-lg bg-charcoal-50 dark:bg-charcoal-800 p-3 text-xs font-mono overflow-x-auto">{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        @endif

        {{-- Admin note (rejection reason, etc.) --}}
        @if ($transaction->admin_note)
            <div class="rounded-2xl border border-red-100 dark:border-red-900/40 bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-700 dark:text-red-300">
                <strong>Admin note:</strong> {{ $transaction->admin_note }}
            </div>
        @endif

        {{-- Actions --}}
        @if (in_array($transaction->status, ['pending', 'processing']))
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm flex flex-wrap gap-3">
                @if (in_array($transaction->type, ['buy', 'deposit']))
                    <form method="POST" action="{{ route('admin.transactions.mark-paid', $transaction) }}">
                        @csrf
                        <x-primary-button>Mark as Paid</x-primary-button>
                    </form>
                @endif

                @if ($transaction->type === 'sell')
                    <form method="POST" action="{{ route('admin.transactions.confirm-sell', $transaction) }}">
                        @csrf
                        <x-primary-button>Confirm Crypto Received</x-primary-button>
                    </form>
                @endif

                @if ($transaction->type === 'withdrawal')
                    <form method="POST" action="{{ route('admin.transactions.approve-withdrawal', $transaction) }}" onsubmit="return confirm('Approve this withdrawal? This finalizes it and removes the funds from the wallet.')">
                        @csrf
                        <x-primary-button>Approve Withdrawal</x-primary-button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.transactions.reject', $transaction) }}" onsubmit="return promptReject(this)">
                    @csrf
                    <input type="hidden" name="reason" value="">
                    <x-danger-button>Reject</x-danger-button>
                </form>
            </div>
        @endif
    </div>

    <script>
        function promptReject(form) {
            const reason = prompt('Rejection reason:');
            if (!reason) return false;
            form.querySelector('input[name="reason"]').value = reason;
            return true;
        }
    </script>
</x-admin-layout>
