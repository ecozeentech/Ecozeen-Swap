<x-app-layout>
    <x-slot name="title">Wallet</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-bold text-charcoal-900 dark:text-white">My Wallets</h2>
            <div class="flex gap-2">
                <a href="{{ route('wallet.fund') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold">Fund Fiat Wallet</a>
                <a href="{{ route('wallet.withdraw') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-charcoal-200 dark:border-charcoal-700 text-sm font-semibold text-charcoal-700 dark:text-charcoal-200">Withdraw</a>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-charcoal-500 dark:text-charcoal-400 mb-3 uppercase tracking-wide">Fiat Balances</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ($wallets->where('currency_type', 'fiat') as $wallet)
                    @php $fiat = $fiatCurrencies->firstWhere('code', $wallet->currency_code); @endphp
                    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-5 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center gap-2">
                            @if ($fiat)
                                <img src="{{ $fiat->logoUrl() }}" class="h-6 w-6 rounded-full object-cover" alt="">
                            @endif
                            <p class="text-xs font-semibold text-charcoal-400">{{ $wallet->currency_code }}</p>
                        </div>
                        <p class="text-2xl font-extrabold text-charcoal-900 dark:text-white mt-2">{{ number_format($wallet->balance, 2) }}</p>
                        @if ((float) $wallet->reserved_balance > 0)
                            <p class="text-xs text-amber-500 mt-1">{{ number_format($wallet->reserved_balance, 2) }} reserved</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-charcoal-500 dark:text-charcoal-400 mb-3 uppercase tracking-wide">Crypto Balances</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ($wallets->where('currency_type', 'crypto') as $wallet)
                    @php $asset = $cryptoAssets->firstWhere('symbol', $wallet->currency_code); @endphp
                    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-5 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if ($asset)
                                    <img src="{{ $asset->logoUrl() }}" class="h-6 w-6 rounded-full object-cover" alt="">
                                @endif
                                <p class="text-xs font-semibold text-charcoal-400">{{ $wallet->currency_code }}</p>
                            </div>
                            @if ($asset)
                                <a href="{{ route('wallet.deposit', $asset) }}" class="text-xs font-semibold text-brand-600 hover:underline">Deposit</a>
                            @endif
                        </div>
                        <p class="text-2xl font-extrabold text-charcoal-900 dark:text-white mt-2">{{ number_format($wallet->balance, 6) }}</p>
                        @if ((float) $wallet->reserved_balance > 0)
                            <p class="text-xs text-amber-500 mt-1">{{ number_format($wallet->reserved_balance, 6) }} reserved</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
            <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800">
                <h3 class="font-semibold text-charcoal-900 dark:text-white">Recent Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-charcoal-400 text-xs uppercase">
                        <tr>
                            <th class="px-5 py-2">Reference</th>
                            <th class="px-5 py-2">Type</th>
                            <th class="px-5 py-2">Amount</th>
                            <th class="px-5 py-2">Status</th>
                            <th class="px-5 py-2">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                        @forelse ($transactions as $tx)
                            <tr>
                                <td class="px-5 py-3 font-mono text-xs">{{ $tx->reference }}</td>
                                <td class="px-5 py-3 capitalize">{{ $tx->type }}</td>
                                <td class="px-5 py-3">{{ number_format($tx->amount, 4) }} {{ $tx->currency_code }}</td>
                                <td class="px-5 py-3">
                                    <span @class([
                                        'text-xs font-medium px-2 py-0.5 rounded-full',
                                        'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' => $tx->status === 'completed',
                                        'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' => in_array($tx->status, ['pending', 'processing']),
                                        'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => in_array($tx->status, ['failed', 'cancelled']),
                                    ])>{{ ucfirst($tx->status) }}</span>
                                </td>
                                <td class="px-5 py-3 text-charcoal-400">{{ $tx->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-6 text-center text-charcoal-400">No transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
