<x-admin-layout>
    <x-slot name="title">Payment Gateways</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach ($gateways as $gateway)
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold">{{ $gateway->name }}</h3>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $gateway->is_active ? 'bg-green-100 text-green-700' : 'bg-charcoal-100 text-charcoal-600' }}">{{ $gateway->is_active ? 'Active' : 'Inactive' }}</span>
                </div>

                <form method="POST" action="{{ route('admin.gateways.update', $gateway) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked($gateway->is_active) class="rounded border-charcoal-300 text-brand-600 focus:ring-brand-500">
                        Enable this gateway
                    </label>

                    @if ($gateway->slug === 'bank_transfer')
                        <div>
                            <x-input-label value="Bank Name" />
                            <x-text-input name="bank_name" value="{{ $gateway->metadata['bank_name'] ?? '' }}" class="mt-1 w-full" />
                        </div>
                        <div>
                            <x-input-label value="Account Number" />
                            <x-text-input name="account_number" value="{{ $gateway->metadata['account_number'] ?? '' }}" class="mt-1 w-full" />
                        </div>
                        <div>
                            <x-input-label value="Account Name" />
                            <x-text-input name="account_name" value="{{ $gateway->metadata['account_name'] ?? '' }}" class="mt-1 w-full" />
                        </div>
                    @else
                        <div>
                            <x-input-label value="Public Key" />
                            <x-text-input name="public_key" value="{{ $gateway->credentials['public_key'] ?? '' }}" class="mt-1 w-full" />
                        </div>
                        <div>
                            <x-input-label value="Secret Key" />
                            <x-password-input name="secret_key" autocomplete="off" />
                        </div>
                        @if ($gateway->slug === 'flutterwave')
                            <div>
                                <x-input-label value="Secret Hash" />
                                <x-password-input name="secret_hash" autocomplete="off" />
                            </div>
                        @endif
                    @endif

                    <x-primary-button class="w-full justify-center py-2.5">Save</x-primary-button>
                </form>
            </div>
        @endforeach
    </div>
</x-admin-layout>
