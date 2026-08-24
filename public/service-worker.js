// Ecozeen Swap service worker
// Caches the app shell (layout CSS/JS + a handful of static routes) so the
// PWA can render an offline fallback instead of a browser error page.
// Financial data (rates, balances, transactions) is always fetched fresh
// over the network and is never served from cache.

const CACHE_NAME = 'ecozeen-swap-shell-v1';
const OFFLINE_URL = '/offline.html';

const APP_SHELL = [
    OFFLINE_URL,
    '/manifest.json',
    '/images/icons/icon-192.png',
    '/images/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(APP_SHELL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
        )).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Never cache API/AJAX, auth, webhook, or Livewire traffic — those must
    // always reflect live balances/rates or fail loudly when offline.
    if (
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/livewire/') ||
        url.pathname.startsWith('/webhook/') ||
        url.pathname.startsWith('/build/')
    ) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );

        return;
    }

    event.respondWith(
        caches.match(request).then((cached) => cached || fetch(request).catch(() => caches.match(OFFLINE_URL)))
    );
});
