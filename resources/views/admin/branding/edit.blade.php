<x-admin-layout>
    <x-slot name="title">Branding &amp; Assets</x-slot>

    <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-6">
        Upload your platform logo, favicon, and social share image. Changes apply everywhere immediately —
        no code changes or redeploys needed.
    </p>

    <form method="POST" action="{{ route('admin.branding.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach ($assetKeys as $key => $label)
                @php
                    $current = $settings->get($key)?->value;
                    $fallback = match ($key) {
                        'site_logo', 'site_logo_dark' => 'images/logo.png',
                        'site_favicon' => 'favicon.ico',
                        default => 'images/icons/icon-512.png',
                    };
                @endphp
                <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                    <h3 class="font-semibold mb-1">{{ $label }}</h3>
                    <p class="text-xs text-charcoal-400 mb-4">
                        @if ($key === 'site_favicon') Recommended: square PNG, at least 192x192px.
                        @elseif ($key === 'site_og_image') Recommended: 1200x630px, shown when links are shared on social media.
                        @else Recommended: transparent PNG or SVG, square or wide.
                        @endif
                    </p>

                    <div class="h-24 w-24 rounded-xl bg-charcoal-50 dark:bg-charcoal-800 flex items-center justify-center overflow-hidden mb-4">
                        <img src="{{ \App\Models\SystemSetting::assetUrl($key, $fallback) }}" alt="{{ $label }}" class="max-h-full max-w-full object-contain">
                    </div>

                    <input type="file" name="{{ $key }}" accept="image/*" class="w-full text-sm mb-3">

                    @if ($current)
                        <form method="POST" action="{{ route('admin.branding.reset', $key) }}" onsubmit="return confirm('Reset {{ $label }} to the default?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Reset to default</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <x-primary-button class="px-8 py-3">Save All Assets</x-primary-button>
        </div>
    </form>
</x-admin-layout>
