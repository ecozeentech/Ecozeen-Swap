<x-app-layout>
    <x-slot name="title">{{ $feature }}</x-slot>

    <div class="flex flex-col items-center justify-center text-center py-20">
        <div class="h-16 w-16 rounded-2xl bg-brand-50 dark:bg-charcoal-800 flex items-center justify-center text-brand-500 mb-6">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <h2 class="text-2xl font-bold text-charcoal-900 dark:text-white">{{ $feature }} — Coming Soon</h2>
        <p class="mt-3 max-w-md text-charcoal-500 dark:text-charcoal-400">{{ $message }}</p>
        <a href="{{ route('dashboard') }}" class="mt-6 inline-flex items-center px-5 py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold">Back to Dashboard</a>
    </div>
</x-app-layout>
