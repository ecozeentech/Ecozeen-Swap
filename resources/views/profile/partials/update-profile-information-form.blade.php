<section>
    <header>
        <h2 class="text-lg font-medium text-charcoal-900 dark:text-charcoal-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-charcoal-500 dark:text-charcoal-400">
            {{ __('Update your photo, contact details, address, and email address.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="flex items-center gap-4">
            @if ($user->avatarUrl())
                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-full object-cover">
            @else
                <span class="h-16 w-16 rounded-full bg-brand-500 text-white flex items-center justify-center text-2xl font-bold">{{ substr($user->name, 0, 1) }}</span>
            @endif
            <div>
                <x-input-label for="avatar" :value="__('Profile Photo')" />
                <input id="avatar" name="avatar" type="file" accept="image/*" class="mt-1 text-sm text-charcoal-600 dark:text-charcoal-300">
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $user->username)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-charcoal-700 dark:text-charcoal-300">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-charcoal-500 dark:text-charcoal-400 hover:text-brand-600">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="border-t border-charcoal-100 dark:border-charcoal-800 pt-6">
            <h3 class="text-sm font-semibold text-charcoal-700 dark:text-charcoal-300 mb-4">{{ __('Address') }}</h3>

            <div class="space-y-4">
                <div>
                    <x-input-label for="address" :value="__('Street Address')" />
                    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $user->address)" autocomplete="street-address" />
                    <x-input-error class="mt-2" :messages="$errors->get('address')" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="city" :value="__('City')" />
                        <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $user->city)" autocomplete="address-level2" />
                        <x-input-error class="mt-2" :messages="$errors->get('city')" />
                    </div>
                    <div>
                        <x-input-label for="state" :value="__('State / Region')" />
                        <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" :value="old('state', $user->state)" autocomplete="address-level1" />
                        <x-input-error class="mt-2" :messages="$errors->get('state')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="country" :value="__('Country')" />
                        <select id="country" name="country" class="mt-1 block w-full rounded-lg border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                            <option value="">{{ __('Select country') }}</option>
                            @foreach ($countries as $code => $name)
                                <option value="{{ $code }}" @selected(old('country', $user->country) === $code)>{{ $name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('country')" />
                    </div>
                    <div>
                        <x-input-label for="postal_code" :value="__('Postal Code')" />
                        <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" :value="old('postal_code', $user->postal_code)" autocomplete="postal-code" />
                        <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-charcoal-500 dark:text-charcoal-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
