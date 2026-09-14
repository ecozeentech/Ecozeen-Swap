<x-guest-layout>
    <x-slot name="title">Create Account</x-slot>

    <h2 class="text-xl font-bold text-charcoal-900 dark:text-white mb-1">Create your account</h2>
    <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-6">Start buying, selling, and swapping crypto with Ecozeen Swap.</p>

    @if ($referrer ?? null)
        <div class="mb-4 rounded-lg bg-brand-50 dark:bg-charcoal-800 border border-brand-100 dark:border-charcoal-700 px-4 py-3 text-sm text-brand-700 dark:text-brand-400 flex items-center gap-2">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4" /></svg>
            You were invited by <strong>&#64;{{ $referrer->username }}</strong>.
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autocomplete="username" placeholder="e.g. john_doe" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone Number (optional)')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-password-input id="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-password-input id="password_confirmation" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label class="inline-flex items-start gap-2">
                <input type="checkbox" name="terms" value="1" required class="mt-0.5 rounded border-charcoal-300 text-brand-600 shadow-sm focus:ring-brand-500">
                <span class="text-sm text-charcoal-600 dark:text-charcoal-400">I agree to the Terms of Service and confirm I will complete KYC verification to unlock higher trading limits.</span>
            </label>
            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-charcoal-500 dark:text-charcoal-400 hover:text-brand-600" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button>
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
