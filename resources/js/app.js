import './bootstrap';

// Alpine.js is intentionally NOT imported/started here. Livewire v3
// bundles its own Alpine instance and boots it automatically via the
// @livewireScripts directive (present in every layout). Having this file
// also `import Alpine from 'alpinejs'; Alpine.start();` created a SECOND,
// independent Alpine instance running alongside Livewire's — the classic
// "Detected multiple instances of Alpine.js running" conflict, which
// silently broke wire:click/wire:model interactions across the admin
// panel (feature toggles, the daily rate form, etc.) because directives
// ended up split across two competing Alpine runtimes.
//
// window.Alpine is still available globally once Livewire's script runs,
// so no functionality is lost — every x-data/x-show/x-model directive in
// the Blade views keeps working exactly as before.
