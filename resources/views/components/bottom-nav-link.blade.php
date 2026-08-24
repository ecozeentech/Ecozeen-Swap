@props(['active' => false, 'icon' => null])

@php
$classes = $active
    ? 'flex flex-1 flex-col items-center justify-center gap-0.5 py-2 text-brand-600 dark:text-brand-400'
    : 'flex flex-1 flex-col items-center justify-center gap-0.5 py-2 text-charcoal-400 dark:text-charcoal-500';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    <span class="h-5 w-5">{!! $icon !!}</span>
    <span class="text-[11px] font-medium">{{ $slot }}</span>
</a>
