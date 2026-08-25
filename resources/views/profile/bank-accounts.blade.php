<x-app-layout>
    <x-slot name="title">Bank Accounts</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-charcoal-900 dark:text-white">Bank Accounts</h2>
                <p class="text-sm text-charcoal-500 dark:text-charcoal-400">Manage the bank accounts we pay out to when you sell crypto or withdraw fiat.</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="text-sm font-semibold text-brand-600 hover:underline">Back to Profile</a>
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm" x-data="{ showForm: {{ $bankAccounts->isEmpty() ? 'true' : 'false' }} }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-charcoal-900 dark:text-white">Your Accounts</h3>
                <button type="button" @click="showForm = !showForm" class="text-sm font-semibold text-brand-600 hover:underline" x-text="showForm ? 'Cancel' : '+ Add Bank Account'"></button>
            </div>

            <div x-show="showForm" x-transition style="display:{{ $bankAccounts->isEmpty() ? 'block' : 'none' }}" class="mb-6 rounded-xl bg-charcoal-50 dark:bg-charcoal-800 p-4">
                <form method="POST" action="{{ route('bank-accounts.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <x-input-label value="Bank Name" />
                        <x-text-input name="bank_name" required class="mt-1 w-full" placeholder="e.g. GTBank" />
                    </div>
                    <div>
                        <x-input-label value="Account Name" />
                        <x-text-input name="account_name" required class="mt-1 w-full" />
                    </div>
                    <div>
                        <x-input-label value="Account Number" />
                        <x-text-input name="account_number" required class="mt-1 w-full" />
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="is_default" value="1" class="rounded border-charcoal-300 text-brand-600 focus:ring-brand-500">
                            Set as default
                        </label>
                    </div>
                    <div class="sm:col-span-2">
                        <x-primary-button type="submit">Save Bank Account</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="space-y-3">
                @forelse ($bankAccounts as $account)
                    <div x-data="{ editing: false }" class="rounded-xl border border-charcoal-100 dark:border-charcoal-800 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-sm text-charcoal-900 dark:text-white">
                                    {{ $account->bank_name }}
                                    @if ($account->is_default)
                                        <span class="ml-2 text-[10px] font-semibold uppercase tracking-wide bg-brand-100 text-brand-700 px-2 py-0.5 rounded-full">Default</span>
                                    @endif
                                </p>
                                <p class="text-xs text-charcoal-400">{{ $account->account_name }} &middot; {{ $account->maskedAccountNumber() }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                @if (! $account->is_default)
                                    <form method="POST" action="{{ route('bank-accounts.set-default', $account) }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-brand-600 hover:underline">Set Default</button>
                                    </form>
                                @endif
                                <button type="button" @click="editing = !editing" class="text-xs font-semibold text-charcoal-500 hover:text-brand-600">Edit</button>
                                <form method="POST" action="{{ route('bank-accounts.destroy', $account) }}" onsubmit="return confirm('Delete this bank account?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        </div>
                        <div x-show="editing" x-transition style="display:none" class="mt-3 pt-3 border-t border-charcoal-100 dark:border-charcoal-800">
                            <form method="POST" action="{{ route('bank-accounts.update', $account) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @csrf @method('PUT')
                                <x-text-input name="bank_name" value="{{ $account->bank_name }}" required class="w-full" />
                                <x-text-input name="account_name" value="{{ $account->account_name }}" required class="w-full" />
                                <x-text-input name="account_number" value="{{ $account->account_number }}" required class="w-full" />
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="is_default" value="1" @checked($account->is_default) class="rounded border-charcoal-300 text-brand-600 focus:ring-brand-500">
                                    Default account
                                </label>
                                <div class="sm:col-span-2">
                                    <x-primary-button type="submit" class="!text-xs !px-4 !py-2">Save Changes</x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-charcoal-400 text-center py-6">No bank accounts added yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
