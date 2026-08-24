@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-charcoal-700 dark:text-charcoal-300']) }}>
    {{ $value ?? $slot }}
</label>
