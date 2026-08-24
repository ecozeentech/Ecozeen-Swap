<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-brand-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-600 focus:bg-brand-600 active:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 dark:focus:ring-offset-charcoal-800 transition ease-in-out duration-150 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
