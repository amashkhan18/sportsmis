const CACHE_NAME = 'hpcl-sports-v1';
const ASSETS_TO_CACHE = [
  '/sportsMIS/',
  '/sportsMIS/public/css/style.css',
  '/sportsMIS/manifest.json',
  // Normally we would cache the CSS framework CDNs here as well for true offline capability
];

// Install Event - Cache assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      console.log('Opened cache');
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
});

// Activate Event - Clean old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Fetch Event - Network first, fallback to cache
self.addEventListener('fetch', event => {
  // Only handle GET requests
  if (event.request.method !== 'GET') return;

  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Clone the response and save it to the cache
        const responseClone = response.clone();
        caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request, responseClone);
        });
        return response;
      })
      .catch(() => caches.match(event.request))
  );
});

// Background Sync for offline score submissions (FR-16)
self.addEventListener('sync', event => {
  if (event.tag === 'sync-scores') {
    event.waitUntil(syncOfflineScores());
  }
});

async function syncOfflineScores() {
    // In a full implementation, we'd pull from IndexedDB and POST to the backend here
    console.log('Syncing offline scores to server...');
}
