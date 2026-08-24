<x-guest-layout>
    <x-slot name="title">Log In</x-slot>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="text-xl font-bold text-charcoal-900 dark:text-white mb-1">Welcome back</h2>
    <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-6">Log in to trade with Ecozeen Swap.</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="login" :value="__('Username or Email')" />
            <x-text-input id="login" class="block mt-1 w-full" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-password-input id="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-charcoal-300 text-brand-600 shadow-sm focus:ring-brand-500" name="remember">
                <span class="ms-2 text-sm text-charcoal-600 dark:text-charcoal-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-charcoal-500 dark:text-charcoal-400 hover:text-brand-600" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <p class="mt-6 text-sm text-center text-charcoal-500 dark:text-charcoal-400">
        Don't have an account? <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:underline">Sign up</a>
    </p>
</x-guest-layout>
