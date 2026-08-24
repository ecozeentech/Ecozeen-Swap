<div
    x-data="{
        show: false,
        dismissed: localStorage.getItem('ecozeen-pwa-dismissed') === 'true',
    }"
    x-init="
        window.addEventListener('pwa-installable', () => { if (!dismissed) show = true });
        window.addEventListener('pwa-installed', () => { show = false });
    "
    x-show="show"
    x-transition
    class="fixed bottom-16 lg:bottom-4 inset-x-0 z-40 flex justify-center px-4"
    style="display:none"
>
    <div class="w-full max-w-md rounded-2xl bg-charcoal-900 text-white shadow-xl px-4 py-3 flex items-center gap-3">
        <div class="h-10 w-10 rounded-xl bg-brand-500 flex items-center justify-center flex-shrink-0 font-bold">E</div>
        <div class="flex-1">
            <p class="text-sm font-semibold">Install Ecozeen Swap</p>
            <p class="text-xs text-charcoal-300">Add to your home screen for a faster, app-like experience.</p>
        </div>
        <button type="button" @click="window.dispatchEvent(new CustomEvent('pwa-install-trigger')); show = false" class="text-xs font-semibold bg-brand-500 hover:bg-brand-600 rounded-lg px-3 py-2">Install</button>
        <button type="button" @click="show = false; dismissed = true; localStorage.setItem('ecozeen-pwa-dismissed', 'true')" class="text-charcoal-400 hover:text-white" aria-label="Dismiss">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>
</div>
