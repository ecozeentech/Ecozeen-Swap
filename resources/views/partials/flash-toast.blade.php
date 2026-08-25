@php
    $rawStatus = session('status');
    $statusText = null;

    if ($rawStatus) {
        $statusText = ucfirst(str_replace('-', ' ', $rawStatus)).'.';
        $statusText = preg_replace('/\bkyc\b/i', 'KYC', $statusText);
        $statusText = preg_replace('/\bip\b/i', 'IP', $statusText);
    }
@endphp

@if ($statusText || $errors->any())
    <div
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 6000)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed top-4 right-4 left-4 sm:left-auto z-[100] w-full sm:w-96 space-y-2"
    >
        @if ($statusText)
            <div class="flex items-start gap-3 rounded-xl bg-white dark:bg-charcoal-900 border border-green-200 dark:border-green-800 shadow-lg px-4 py-3">
                <span class="h-8 w-8 flex-shrink-0 rounded-full bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400 flex items-center justify-center">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                </span>
                <p class="text-sm font-medium text-charcoal-800 dark:text-charcoal-100 flex-1 pt-1">{{ $statusText }}</p>
                <button type="button" @click="show = false" class="text-charcoal-400 hover:text-charcoal-600 dark:hover:text-charcoal-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="flex items-start gap-3 rounded-xl bg-white dark:bg-charcoal-900 border border-red-200 dark:border-red-800 shadow-lg px-4 py-3">
                <span class="h-8 w-8 flex-shrink-0 rounded-full bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
                <ul class="text-sm font-medium text-charcoal-800 dark:text-charcoal-100 flex-1 pt-1 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" @click="show = false" class="text-charcoal-400 hover:text-charcoal-600 dark:hover:text-charcoal-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        @endif
    </div>
@endif
