@php
    // The logo is rendered purely as an image asset (never as a text
    // wordmark). Admins can replace it from /admin/branding; otherwise it
    // falls back to the bundled public/images/logo.png.
    $logoUrl = \App\Models\SystemSetting::assetUrl('site_logo', 'images/logo.png');
@endphp

<img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" {{ $attributes->merge(['class' => 'h-9 w-9 object-contain']) }}>
