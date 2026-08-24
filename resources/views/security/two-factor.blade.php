<x-app-layout>
    <x-slot name="title">Two-Factor Authentication</x-slot>

    <div class="max-w-lg mx-auto rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-charcoal-900 dark:text-white mb-1">Two-Factor Authentication</h2>
        <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-4">Add an extra layer of security using Google Authenticator or a compatible app.</p>

        @if (session('status') === 'two-factor-enabled')
            <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">2FA has been enabled on your account.</div>
        @elseif (session('status') === 'two-factor-disabled')
            <div class="mb-4 rounded-lg bg-charcoal-50 dark:bg-charcoal-800 px-4 py-3 text-sm">2FA has been disabled.</div>
        @elseif (session('status') === 'recovery-codes-regenerated')
            <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">New recovery codes generated. Save them somewhere safe.</div>
        @endif

        @if ($enabled)
            <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300 mb-4">
                Two-factor authentication is <strong>enabled</strong> on your account.
            </div>

            @if ($recoveryCodes)
                <div class="mb-4">
                    <p class="text-sm font-semibold text-charcoal-700 dark:text-charcoal-300 mb-2">Recovery Codes</p>
                    <div class="grid grid-cols-2 gap-2 rounded-lg bg-charcoal-50 dark:bg-charcoal-800 p-3 font-mono text-xs">
                        @foreach ($recoveryCodes as $code)
                            <span>{{ $code }}</span>
                        @endforeach
                    </div>
                    <form method="POST" action="{{ route('security.two-factor.recovery-codes') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-brand-600 hover:underline">Regenerate recovery codes</button>
                    </form>
                </div>
            @endif

            <form method="POST" action="{{ route('security.two-factor.disable') }}" class="space-y-3">
                @csrf
                @method('DELETE')
                <x-input-label for="password" value="Confirm password to disable 2FA" />
                <x-password-input id="password" name="password" required />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
                <x-danger-button class="w-full justify-center py-3">Disable 2FA</x-danger-button>
            </form>
        @else
            <div class="flex flex-col items-center text-center">
                <div class="bg-white p-3 rounded-lg border border-charcoal-100">
                    {!! $qrCodeSvg !!}
                </div>
                <p class="text-xs text-charcoal-400 mt-2 break-all">Secret: {{ $secret }}</p>
            </div>

            <form method="POST" action="{{ route('security.two-factor.confirm') }}" class="mt-4 space-y-3">
                @csrf
                <x-input-label for="code" value="Enter the 6-digit code from your app" />
                <x-text-input id="code" name="code" type="text" inputmode="numeric" maxlength="6" required class="w-full text-center tracking-widest text-lg" />
                <x-input-error :messages="$errors->get('code')" class="mt-1" />
                <x-primary-button class="w-full justify-center py-3">Enable 2FA</x-primary-button>
            </form>
        @endif
    </div>
</x-app-layout>
