// PWA bootstrap: registers the service worker and wires up the
// "Add to Home Screen" install prompt banner.
(function () {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/service-worker.js').catch((error) => {
                console.warn('Ecozeen Swap: service worker registration failed.', error);
            });
        });
    }

    let deferredPrompt = null;

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        window.dispatchEvent(new CustomEvent('pwa-installable'));
    });

    window.addEventListener('pwa-install-trigger', async () => {
        if (!deferredPrompt) {
            return;
        }

        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
    });

    window.addEventListener('appinstalled', () => {
        window.dispatchEvent(new CustomEvent('pwa-installed'));
    });
})();
