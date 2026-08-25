<x-admin-layout>
    <x-slot name="title">{{ $user->name }}</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        @if ($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" class="h-14 w-14 rounded-full object-cover" alt="">
                        @else
                            <span class="h-14 w-14 rounded-full bg-brand-500 text-white flex items-center justify-center text-xl font-bold">{{ substr($user->name, 0, 1) }}</span>
                        @endif
                        <div>
                            <h2 class="text-lg font-bold">{{ $user->name }}</h2>
                            <p class="text-sm text-charcoal-400">{{ $user->username }} &middot; {{ $user->email }} &middot; {{ $user->phone }}</p>
                            @if ($user->fullAddress())
                                <p class="text-xs text-charcoal-400 mt-1">{{ $user->fullAddress() }}</p>
                            @endif
                        </div>
                    </div>
                    @if ($user->is_suspended)
                        <form method="POST" action="{{ route('admin.users.unsuspend', $user) }}">
                            @csrf
                            <x-secondary-button type="submit">Unsuspend</x-secondary-button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.users.suspend', $user) }}" onsubmit="return confirm('Suspend this user?')">
                            @csrf
                            <input type="hidden" name="reason" value="Suspended by admin">
                            <x-danger-button type="submit">Suspend</x-danger-button>
                        </form>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex items-end gap-3">
                    @csrf
                    @method('PUT')
                    <div class="flex-1">
                        <x-input-label value="Daily Trade Limit (USD)" />
                        <x-text-input name="daily_trade_limit" type="number" step="0.01" value="{{ $user->daily_trade_limit }}" class="mt-1 w-full" />
                    </div>
                    <x-primary-button type="submit">Update</x-primary-button>
                </form>
            </div>

            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                <h3 class="font-semibold mb-4">Profile Details</h3>
                <form method="POST" action="{{ route('admin.users.update-profile', $user) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label value="Full Name" />
                        <x-text-input name="name" value="{{ $user->name }}" required class="mt-1 w-full" />
                    </div>
                    <div>
                        <x-input-label value="Phone" />
                        <x-text-input name="phone" value="{{ $user->phone }}" class="mt-1 w-full" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label value="Address" />
                        <x-text-input name="address" value="{{ $user->address }}" class="mt-1 w-full" />
                    </div>
                    <div>
                        <x-input-label value="City" />
                        <x-text-input name="city" value="{{ $user->city }}" class="mt-1 w-full" />
                    </div>
                    <div>
                        <x-input-label value="State / Region" />
                        <x-text-input name="state" value="{{ $user->state }}" class="mt-1 w-full" />
                    </div>
                    <div>
                        <x-input-label value="Country" />
                        <select name="country" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white">
                            <option value="">Select country</option>
                            @foreach (config('countries') as $code => $name)
                                <option value="{{ $code }}" @selected($user->country === $code)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Postal Code" />
                        <x-text-input name="postal_code" value="{{ $user->postal_code }}" class="mt-1 w-full" />
                    </div>
                    <div>
                        <x-input-label value="KYC Status" />
                        <select name="kyc_status" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white">
                            @foreach (\App\Models\User::kycStatusOptions() as $status => $label)
                                <option value="{{ $status }}" @selected($user->kyc_status === $status)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 flex justify-end">
                        <x-primary-button type="submit">Save Profile</x-primary-button>
                    </div>
                </form>
            </div>

            @if (auth()->user()->hasRole('super-admin'))
                <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                    <h3 class="font-semibold mb-4">Role &amp; Permissions</h3>
                    <form method="POST" action="{{ route('admin.users.update-role', $user) }}" class="flex items-end gap-3">
                        @csrf
                        @method('PUT')
                        <div class="flex-1">
                            <x-input-label value="Role" />
                            <select name="role" class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white">
                                @foreach (['user', 'admin', 'super-admin'] as $role)
                                    <option value="{{ $role }}" @selected($user->hasRole($role))>{{ ucwords(str_replace('-', ' ', $role)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-primary-button type="submit" onclick="return confirm('Change this user&#39;s role?')">Update Role</x-primary-button>
                    </form>
                </div>
            @endif

            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
                <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800"><h3 class="font-semibold">Wallets</h3></div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-5">
                    @foreach ($user->wallets as $wallet)
                        <div class="rounded-xl bg-charcoal-50 dark:bg-charcoal-800 px-4 py-3">
                            <p class="text-xs text-charcoal-400">{{ $wallet->currency_code }}</p>
                            <p class="font-bold">{{ number_format($wallet->balance, 6) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
                <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800"><h3 class="font-semibold">KYC Documents</h3></div>
                <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                    @forelse ($user->kycDocuments as $doc)
                        <div class="px-5 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold capitalize">{{ str_replace('_', ' ', $doc->document_type) }}</p>
                                <p class="text-xs text-charcoal-400">{{ $doc->created_at->diffForHumans() }}</p>
                            </div>
                            @if ($doc->status === 'pending')
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('admin.kyc.approve', $doc) }}"><input type="hidden" name="_dummy"> @csrf<x-primary-button class="!text-[10px] !px-3 !py-1.5">Approve</x-primary-button></form>
                                    <form method="POST" action="{{ route('admin.kyc.reject', $doc) }}" onsubmit="return promptRejectReason(this)">@csrf<input type="hidden" name="reason" value=""><x-danger-button class="!text-[10px] !px-3 !py-1.5">Reject</x-danger-button></form>
                                </div>
                            @else
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize {{ $doc->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $doc->status }}</span>
                            @endif
                        </div>
                    @empty
                        <p class="px-5 py-6 text-sm text-charcoal-400 text-center">No documents uploaded.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
                <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800"><h3 class="font-semibold">Transactions</h3></div>
                <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800 max-h-96 overflow-y-auto">
                    @foreach ($transactions as $tx)
                        <div class="px-5 py-3 flex items-center justify-between text-sm">
                            <span class="capitalize">{{ $tx->type }} &middot; {{ $tx->reference }}</span>
                            <span>{{ number_format($tx->amount, 4) }} {{ $tx->currency_code }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
            <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800"><h3 class="font-semibold">Activity Log</h3></div>
            <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800 max-h-[40rem] overflow-y-auto">
                @foreach ($activityLogs as $log)
                    <div class="px-5 py-3">
                        <p class="text-sm font-semibold capitalize">{{ str_replace('_', ' ', $log->action) }}</p>
                        <p class="text-xs text-charcoal-400">{{ $log->ip_address }} &middot; {{ $log->created_at->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        function promptRejectReason(form) {
            const reason = prompt('Rejection reason:');
            if (!reason) return false;
            form.querySelector('input[name="reason"]').value = reason;
            return true;
        }
    </script>
</x-admin-layout>
