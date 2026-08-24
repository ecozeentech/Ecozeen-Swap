@props(['active' => false, 'icon' => null])

@php
$classes = $active
    ? 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold bg-brand-500 text-white shadow-sm'
    : 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-charcoal-600 dark:text-charcoal-300 hover:bg-brand-50 dark:hover:bg-charcoal-800 hover:text-brand-600 dark:hover:text-brand-400';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <span class="h-5 w-5 flex-shrink-0">{!! $icon !!}</span>
    @endif
    <span>{{ $slot }}</span>
</a>
