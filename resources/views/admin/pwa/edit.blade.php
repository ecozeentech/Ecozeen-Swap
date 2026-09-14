<x-admin-layout>
    <x-slot name="title">PWA &amp; App Icons</x-slot>

    <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-6">
        Manage the installable "Add to Home Screen" experience — the app icon, splash icon, name, and
        colors shown while the app is launching. Changes apply the next time someone (re)installs the
        app or refreshes the manifest — no code changes or redeploys needed.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        @foreach ($assetKeys as $key => $label)
            @php
                $current = $settings->get($key)?->value;
                $fallback = $key === 'pwa_icon_192' ? 'images/icons/icon-192.png' : 'images/icons/icon-512.png';
            @endphp
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                <h3 class="font-semibold mb-1">{{ $label }}</h3>
                <p class="text-xs text-charcoal-400 mb-4">
                    @if ($key === 'pwa_icon_192') Recommended: square PNG, exactly 192×192px.
                    @elseif ($key === 'pwa_icon_512') Recommended: square PNG, exactly 512×512px. Used as the app icon and the source for the splash screen shown while the app launches.
                    @else Optional. A square PNG with the logo kept inside the centre ~80% "safe zone" — Android crops this to circles/squares/rounded-squares depending on the device.
                    @endif
                </p>

                <div class="h-24 w-24 rounded-xl bg-charcoal-50 dark:bg-charcoal-800 flex items-center justify-center overflow-hidden mb-4">
                    @if ($key === 'pwa_maskable_icon' && ! $current)
                        <span class="text-xs text-charcoal-400 text-center px-2">Not set — falls back to the 512×512 app icon</span>
                    @else
                        <img src="{{ \App\Models\SystemSetting::assetUrl($key, $fallback) }}" alt="{{ $label }}" class="max-h-full max-w-full object-contain">
                    @endif
                </div>

                {{-- Each card is its own independent <form> — forms must never be
                     nested inside another <form>, see BrandingController. --}}
                <form method="POST" action="{{ route('admin.pwa.update') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <input type="file" name="{{ $key }}" accept="image/*" class="w-full text-sm">
                    <x-input-error :messages="$errors->get($key)" />
                    <x-secondary-button type="submit" class="w-full justify-center">Upload</x-secondary-button>
                </form>

                @if ($current)
                    <form method="POST" action="{{ route('admin.pwa.reset', $key) }}" onsubmit="return confirm('Reset {{ $label }} to the default?')" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Reset to default</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
            <h3 class="font-semibold mb-4">App Details &amp; Colors</h3>
            <form method="POST" action="{{ route('admin.pwa.update') }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="App Name" />
                        <x-text-input name="pwa_app_name" value="{{ $settings->get('pwa_app_name')?->value }}" class="mt-1 w-full" placeholder="Ecozeen Swap" maxlength="45" />
                        <p class="text-xs text-charcoal-400 mt-1">Shown on the install prompt and splash screen.</p>
                    </div>
                    <div>
                        <x-input-label value="Short Name" />
                        <x-text-input name="pwa_short_name" value="{{ $settings->get('pwa_short_name')?->value }}" class="mt-1 w-full" placeholder="EcozeenSwap" maxlength="12" />
                        <p class="text-xs text-charcoal-400 mt-1">Shown under the icon on the home screen — keep it short.</p>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Theme Color" />
                        <div class="mt-1 flex items-center gap-2">
                            <input type="color" name="pwa_theme_color_picker" value="{{ $settings->get('pwa_theme_color')?->value ?: '#2563EB' }}" class="h-10 w-12 rounded border border-charcoal-200 dark:border-charcoal-600" oninput="this.nextElementSibling.value = this.value">
                            <x-text-input name="pwa_theme_color" value="{{ $settings->get('pwa_theme_color')?->value ?: '#2563EB' }}" class="w-full" placeholder="#2563EB" />
                        </div>
                        <p class="text-xs text-charcoal-400 mt-1">Browser toolbar / status bar color.</p>
                    </div>
                    <div>
                        <x-input-label value="Background Color" />
                        <div class="mt-1 flex items-center gap-2">
                            <input type="color" name="pwa_background_color_picker" value="{{ $settings->get('pwa_background_color')?->value ?: '#F8FAFC' }}" class="h-10 w-12 rounded border border-charcoal-200 dark:border-charcoal-600" oninput="this.nextElementSibling.value = this.value">
                            <x-text-input name="pwa_background_color" value="{{ $settings->get('pwa_background_color')?->value ?: '#F8FAFC' }}" class="w-full" placeholder="#F8FAFC" />
                        </div>
                        <p class="text-xs text-charcoal-400 mt-1">Splash screen background while the app loads.</p>
                    </div>
                </div>
                <x-primary-button class="w-full justify-center py-2.5">Save App Details</x-primary-button>
            </form>
        </div>

        <div class="rounded-2xl p-6 shadow-sm flex flex-col items-center justify-center text-center" style="background-color: {{ $settings->get('pwa_background_color')?->value ?: '#F8FAFC' }}">
            <div class="h-20 w-20 rounded-2xl overflow-hidden shadow-lg mb-4 bg-white flex items-center justify-center">
                <img src="{{ \App\Models\SystemSetting::assetUrl('pwa_icon_512', 'images/icons/icon-512.png') }}" alt="App icon preview" class="max-h-full max-w-full object-contain">
            </div>
            <p class="font-bold" style="color: {{ $settings->get('pwa_theme_color')?->value ?: '#2563EB' }}">{{ $settings->get('pwa_short_name')?->value ?: 'EcozeenSwap' }}</p>
            <p class="text-xs text-charcoal-500 mt-1">Splash screen preview</p>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-dashed border-charcoal-200 dark:border-charcoal-700 p-4 text-xs text-charcoal-400">
        The live manifest reflecting these settings is always available at
        <a href="{{ route('pwa.manifest') }}" target="_blank" class="text-brand-600 hover:underline font-mono">{{ route('pwa.manifest') }}</a>.
    </div>
</x-admin-layout>
