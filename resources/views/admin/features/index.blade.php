<x-admin-layout>
    <x-slot name="title">Feature Toggles</x-slot>

    <p class="text-sm text-charcoal-500 dark:text-charcoal-400 mb-6">
        Disable any feature instantly to show a "Coming Soon" notice on the frontend. Customize the default message under
        <a href="{{ route('admin.settings.index') }}" class="text-brand-600 font-semibold hover:underline">Settings</a>.
    </p>

    <livewire:feature-toggle-grid />
</x-admin-layout>
