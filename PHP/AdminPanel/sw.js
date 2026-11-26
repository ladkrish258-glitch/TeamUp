// TeamUp v4 — service worker disabled for development
self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  // Clear all caches and unregister
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(keys.map((k) => caches.delete(k))))
      .then(() => self.registration.unregister())
  );
});

self.addEventListener('fetch', (event) => {
  // Always fetch from network
  event.respondWith(fetch(event.request));
});
