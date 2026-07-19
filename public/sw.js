// eSchool SaaS Service Worker
// Strategy: cache-first for static, network-first for HTML pages, runtime cache for visited pages.
// Supports Background Sync for offline mode.

const CACHE_VERSION = 'eschool-v2';
const RUNTIME_CACHE = 'eschool-runtime-v2';
const STATIC_CACHE = 'eschool-static-v2';
const CDN_CACHE = 'eschool-cdn-v2';

const STATIC_ASSETS = [
  '/',
  '/manifest.webmanifest',
  '/js/offline-db.js',
  '/js/offline-sync.js',
];

const CDN_URLS = [
  'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
  'https://cdn.jsdelivr.net/npm/chart.js',
];

const ADMIN_PAGES = [
  '/admin/dashboard',
  '/admin/attendance',
  '/admin/students',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    Promise.all([
      caches.open(CACHE_VERSION).then((cache) => cache.addAll(STATIC_ASSETS).catch(() => null)),
      caches.open(CDN_CACHE).then((cache) => Promise.allSettled(
        CDN_URLS.map(url => cache.add(url).catch(() => null))
      )),
    ])
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.filter(k =>
        ![CACHE_VERSION, RUNTIME_CACHE, STATIC_CACHE, CDN_CACHE].includes(k)
      ).map(k => caches.delete(k))
    ))
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  if (request.method !== 'GET' || url.origin !== location.origin) return;

  if (url.pathname.startsWith('/api/v1/auth/') || url.pathname === '/admin/login' || url.pathname === '/super/login') {
    return;
  }

  if (CDN_URLS.some(cdn => url.href.startsWith(cdn.split('/dist')[0] || cdn))) {
    event.respondWith(
      caches.match(request).then((cached) => cached || fetch(request).then((res) => {
        const copy = res.clone();
        caches.open(CDN_CACHE).then((c) => c.put(request, copy));
        return res;
      }))
    );
    return;
  }

  if (request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(request)
        .then((res) => {
          const copy = res.clone();
          caches.open(RUNTIME_CACHE).then((c) => c.put(request, copy));
          return res;
        })
        .catch(() => caches.match(request).then(r => r || caches.match('/')))
    );
    return;
  }

  event.respondWith(
    caches.match(request).then((cached) => {
      return cached || fetch(request).then((res) => {
        const copy = res.clone();
        if (url.pathname.match(/\.(js|css|woff2?|png|svg|webp|ico)$/)) {
          caches.open(STATIC_CACHE).then((c) => c.put(request, copy));
        } else {
          caches.open(RUNTIME_CACHE).then((c) => c.put(request, copy));
        }
        return res;
      }).catch(() => cached);
    })
  );
});

self.addEventListener('sync', (event) => {
  if (event.tag === 'eschool-offline-sync') {
    event.waitUntil(syncPendingData());
  }
});

async function syncPendingData() {
  try {
    const clients = await self.clients.matchAll({ type: 'window' });
    if (clients.length > 0) {
      clients[0].postMessage({ type: 'TRIGGER_SYNC' });
    }
  } catch (e) {
    console.error('[SW] Background sync failed:', e);
  }
}

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
