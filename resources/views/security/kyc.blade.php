<x-app-layout>
    <x-slot name="title">KYC Verification</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-lg font-bold text-charcoal-900 dark:text-white">KYC Verification</h2>
                <span @class([
                    'text-xs font-medium px-2 py-1 rounded-full capitalize',
                    'bg-green-100 text-green-700' => auth()->user()->kyc_status === 'verified',
                    'bg-amber-100 text-amber-700' => in_array(auth()->user()->kyc_status, ['unverified', 'pending']),
                    'bg-red-100 text-red-700' => auth()->user()->kyc_status === 'rejected',
                ])>{{ auth()->user()->kycLabel() }}</span>
            </div>
            <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-4">
                @if (auth()->user()->kyc_status !== 'verified')
                    KYC is entirely optional &mdash; it never blocks trading. Completing it simply raises your daily trading limit from
                    <strong>${{ number_format(auth()->user()->daily_trade_limit, 2) }}</strong> to a much higher tier.
                @else
                    Your identity has been verified. You are trading with the highest limits available.
                @endif
            </p>

            <form method="POST" action="{{ route('security.kyc.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="document_type" value="Document Type" />
                    <select id="document_type" name="document_type" required class="mt-1 w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                        <option value="id_card">National ID Card</option>
                        <option value="passport">Passport</option>
                        <option value="drivers_license">Driver's License</option>
                        <option value="selfie">Selfie with ID</option>
                        <option value="proof_of_address">Proof of Address</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="file" value="Upload File" />
                    <input id="file" name="file" type="file" accept="image/*,.pdf" required class="mt-1 w-full text-sm text-charcoal-600 dark:text-charcoal-300">
                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                </div>
                <x-primary-button class="w-full justify-center py-3">Upload Document</x-primary-button>
            </form>
        </div>

        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 shadow-sm">
            <div class="px-5 py-4 border-b border-charcoal-100 dark:border-charcoal-800">
                <h3 class="font-semibold text-charcoal-900 dark:text-white">Submitted Documents</h3>
            </div>
            <div class="divide-y divide-charcoal-100 dark:divide-charcoal-800">
                @forelse ($documents as $doc)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold capitalize">{{ str_replace('_', ' ', $doc->document_type) }}</p>
                            <p class="text-xs text-charcoal-400">{{ $doc->created_at->diffForHumans() }}</p>
                            @if ($doc->status === 'rejected' && $doc->rejection_reason)
                                <p class="text-xs text-red-500 mt-1">{{ $doc->rejection_reason }}</p>
                            @endif
                        </div>
                        <span @class([
                            'text-xs font-medium px-2 py-0.5 rounded-full capitalize',
                            'bg-green-100 text-green-700' => $doc->status === 'approved',
                            'bg-amber-100 text-amber-700' => $doc->status === 'pending',
                            'bg-red-100 text-red-700' => $doc->status === 'rejected',
                        ])>{{ $doc->status }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-charcoal-400 text-center">No documents uploaded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
