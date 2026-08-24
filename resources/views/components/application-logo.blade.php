@props(['withText' => false])

@php
    $customLogo = \App\Models\SystemSetting::get('site_logo');
    $logoUrl = $customLogo ? \App\Models\SystemSetting::assetUrl('site_logo', '') : null;
@endphp

<div class="flex items-center gap-2">
    @if ($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" {{ $attributes->merge(['class' => 'h-9 w-9 object-contain rounded-lg']) }}>
    @else
        <svg viewBox="0 0 100 116" xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => 'h-9 w-9']) }}>
            <path d="M50 2 L96 20 V58 C96 88 76 106 50 114 C24 106 4 88 4 58 V20 Z" fill="#1c1f27" />
            <text x="50" y="70" font-family="Arial, sans-serif" font-size="46" font-weight="700" fill="white" text-anchor="middle">E</text>
        </svg>
    @endif
    @if ($withText)
        <span class="text-xl font-extrabold tracking-tight text-charcoal-900 dark:text-white">
            Ecozeen<span class="text-brand-500">Swap</span>
        </span>
    @endif
</div>
