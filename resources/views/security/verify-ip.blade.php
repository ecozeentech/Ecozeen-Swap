<x-app-layout>
    <x-slot name="title">Verify This Device</x-slot>

    <div class="max-w-lg mx-auto rounded-2xl border border-amber-200 dark:border-amber-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm text-center">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-500 mb-4">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        </div>
        <h2 class="text-lg font-bold text-charcoal-900 dark:text-white mb-2">New Sign-In Location Detected</h2>
        <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-4">
            We noticed activity from a new IP address (<span class="font-mono">{{ $ip }}</span>). For your security, trading has
            been temporarily paused until you confirm this was you. We've emailed a confirmation link — you can also enter the
            6-digit code below.
        </p>

        <form method="POST" action="{{ route('security.verify-ip', $trustedIp) }}" class="flex gap-2">
            @csrf
            <x-text-input name="code" type="text" inputmode="numeric" maxlength="6" required class="flex-1 text-center tracking-widest" placeholder="000000" />
            <x-primary-button>Verify</x-primary-button>
        </form>
        @error('code')
            <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
        @enderror
    </div>
</x-app-layout>
