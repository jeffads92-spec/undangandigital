// WEDDING DIGITAL - SERVICE WORKER
// Progressive Web App untuk offline functionality

const CACHE_NAME = 'wedding-digital-v2.0';
const STATIC_CACHE = 'static-v2';
const DYNAMIC_CACHE = 'dynamic-v2';

// Files to cache on install
const STATIC_FILES = [
  '/',
  '/index.php',
  '/manifest.json',
  '/assets/css/main.css',
  '/assets/js/main.js',
  '/assets/icons/icon-192x192.png',
  '/assets/icons/icon-512x512.png',
  '/assets/images/offline.jpg'
];

// Install event
self.addEventListener('install', event => {
  console.log('[Service Worker] Installing...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => {
        console.log('[Service Worker] Caching static files');
        return cache.addAll(STATIC_FILES);
      })
      .then(() => {
        console.log('[Service Worker] Installation complete');
        return self.skipWaiting();
      })
  );
});

// Activate event
self.addEventListener('activate', event => {
  console.log('[Service Worker] Activating...');
  
  // Clean up old caches
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      console.log('[Service Worker] Activation complete');
      return self.clients.claim();
    })
  );
});

// Fetch event - Network first, then cache
self.addEventListener('fetch', event => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') return;
  
  // Skip chrome-extension requests
  if (event.request.url.indexOf('chrome-extension') !== -1) return;
  
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Clone the response
        const responseClone = response.clone();
        
        // Cache dynamic responses
        if (event.request.url.indexOf('http') !== -1 && 
            !event.request.url.includes('/admin') &&
            !event.request.url.includes('.php')) {
          caches.open(DYNAMIC_CACHE)
            .then(cache => {
              cache.put(event.request, responseClone);
            });
        }
        
        return response;
      })
      .catch(() => {
        // If network fails, try cache
        return caches.match(event.request)
          .then(cachedResponse => {
            if (cachedResponse) {
              return cachedResponse;
            }
            
            // If not in cache, show offline page for HTML requests
            if (event.request.headers.get('accept').includes('text/html')) {
              return caches.match('/offline.html');
            }
            
            // Return offline image for image requests
            if (event.request.headers.get('accept').includes('image')) {
              return caches.match('/assets/images/offline.jpg');
            }
          });
      })
  );
});

// Background sync for form submissions
self.addEventListener('sync', event => {
  if (event.tag === 'rsvp-sync') {
    event.waitUntil(syncRSVPData());
  }
  
  if (event.tag === 'message-sync') {
    event.waitUntil(syncMessages());
  }
});

// Sync RSVP data when online
async function syncRSVPData() {
  const db = await openRSVPIndexedDB();
  const pendingRSVP = await db.getAll('pending_rsvp');
  
  for (const rsvp of pendingRSVP) {
    try {
      const response = await fetch('/api/rsvp/submit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(rsvp)
      });
      
      if (response.ok) {
        await db.delete('pending_rsvp', rsvp.id);
      }
    } catch (error) {
      console.error('Failed to sync RSVP:', error);
    }
  }
}

// Open IndexedDB for offline storage
function openRSVPIndexedDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('wedding_offline', 1);
    
    request.onupgradeneeded = event => {
      const db = event.target.result;
      
      if (!db.objectStoreNames.contains('pending_rsvp')) {
        db.createObjectStore('pending_rsvp', { keyPath: 'id' });
      }
      
      if (!db.objectStoreNames.contains('pending_messages')) {
        db.createObjectStore('pending_messages', { keyPath: 'id' });
      }
    };
    
    request.onsuccess = event => {
      resolve(event.target.result);
    };
    
    request.onerror = event => {
      reject(event.target.error);
    };
  });
}