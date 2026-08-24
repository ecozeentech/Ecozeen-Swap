<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white dark:bg-charcoal-800 border border-charcoal-300 dark:border-charcoal-600 rounded-lg font-semibold text-xs text-charcoal-700 dark:text-charcoal-200 uppercase tracking-widest shadow-sm hover:bg-charcoal-50 dark:hover:bg-charcoal-700 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 dark:focus:ring-offset-charcoal-800 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
