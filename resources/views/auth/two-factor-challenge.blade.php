<x-guest-layout>
    <x-slot name="title">Two-Factor Verification</x-slot>

    <h2 class="text-xl font-bold text-charcoal-900 dark:text-white mb-1">Two-Factor Verification</h2>
    <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-6">Enter the 6-digit code from your authenticator app, or one of your recovery codes.</p>

    <form method="POST" action="{{ route('two-factor.login') }}">
        @csrf
        <x-input-label for="code" :value="__('Authentication Code')" />
        <x-text-input id="code" class="block mt-1 w-full text-center tracking-widest text-lg" type="text" name="code" inputmode="numeric" required autofocus />
        <x-input-error :messages="$errors->get('code')" class="mt-2" />

        <x-primary-button class="w-full justify-center py-3 mt-6">Verify</x-primary-button>
    </form>
</x-guest-layout>
