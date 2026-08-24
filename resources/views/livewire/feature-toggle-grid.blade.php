<div>
    <div x-data="{ show: false, message: '' }"
         x-on:feature-toggled.window="show = true; message = $event.detail.feature + ' ' + ($event.detail.enabled ? 'enabled' : 'disabled'); setTimeout(() => show = false, 2500)"
         x-show="show" x-transition
         class="mb-4 rounded-lg bg-brand-50 dark:bg-brand-900/30 border border-brand-200 dark:border-brand-800 px-4 py-2 text-sm text-brand-700 dark:text-brand-300"
         style="display:none">
        <span x-text="message" class="capitalize"></span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($features as $key => $label)
            <div class="flex items-center justify-between rounded-xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 px-4 py-4 shadow-sm">
                <div>
                    <p class="font-semibold text-sm text-charcoal-900 dark:text-white">{{ $label }}</p>
                    <p class="text-xs {{ $flags[$key] ?? true ? 'text-green-600' : 'text-red-500' }}">
                        {{ ($flags[$key] ?? true) ? 'Enabled' : 'Disabled — Coming Soon' }}
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="toggle('{{ $key }}')"
                    role="switch"
                    aria-checked="{{ ($flags[$key] ?? true) ? 'true' : 'false' }}"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors {{ ($flags[$key] ?? true) ? 'bg-brand-500' : 'bg-charcoal-300 dark:bg-charcoal-700' }}"
                >
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ ($flags[$key] ?? true) ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
            </div>
        @endforeach
    </div>
</div>
