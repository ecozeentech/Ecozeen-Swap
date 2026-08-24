@props([
    'title' => config('app.name'),
    'description' => 'Ecozeen Swap — buy, sell, and swap crypto directly with Ecozeen Tech Ltd, your single trusted vendor.',
    'image' => null,
    'type' => 'website',
])

@php
    $ogImage = $image ?: \App\Models\SystemSetting::assetUrl('site_og_image', 'images/icons/icon-512.png');
@endphp

<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ url()->current() }}">

<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="{{ config('app.name') }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $ogImage }}">
