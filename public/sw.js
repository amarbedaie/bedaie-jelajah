/*
 * BeDaie Jelajah — service worker.
 *
 * Sengaja konservatif: rangka aplikasi dan aset dibina di-cache supaya
 * halaman terbuka pantas pada isyarat lemah di masjid, tetapi data langsung
 * (pendaftaran, kehadiran, kapasiti) SENTIASA diambil dari rangkaian.
 * Menghidangkan kiraan tempat yang lapuk lebih buruk daripada menunggu.
 */
const VERSION = 'jelajah-v1';
const OFFLINE_URL = '/luar-talian';

const PRECACHE = [OFFLINE_URL, '/img/icon-192.png', '/img/icon-512.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(VERSION)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
            .catch(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== VERSION).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

function isAsset(url) {
    return url.pathname.startsWith('/build/')
        || url.pathname.startsWith('/img/')
        || url.pathname.startsWith('/storage/poster/')
        || url.pathname.startsWith('/storage/qr/');
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Jangan sekali-kali cache Livewire, borang atau muat turun sijil.
    if (url.pathname.startsWith('/livewire')
        || url.pathname.startsWith('/admin')
        || url.pathname.startsWith('/check-in')
        || url.pathname.includes('/muat-turun')) {
        return;
    }

    // Aset dibina: cache-first (namanya sudah mengandungi hash).
    if (isAsset(url)) {
        event.respondWith(
            caches.match(request).then((hit) => hit || fetch(request).then((res) => {
                const copy = res.clone();
                caches.open(VERSION).then((c) => c.put(request, copy));
                return res;
            }))
        );
        return;
    }

    // Halaman: network-first, jatuh ke halaman luar talian bila gagal.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
    }
});
