@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-charcoal-200 dark:border-charcoal-600 dark:bg-charcoal-800 dark:text-white focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-lg shadow-sm']) }}>
