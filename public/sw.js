// Service Worker pour BAOBAB Express PWA
const CACHE_NAME = 'baobab-express-v1';
const OFFLINE_URL = '/offline.html';

// Ressources à mettre en cache immédiatement
const PRECACHE_ASSETS = [
    '/',
    '/offline.html',
    '/manifest.json',
    '/images/logo.png',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png',
    'https://fonts.bunny.net/css?family=Nunito',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
    'https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css',
];

// Ressources à mettre en cache lors de la navigation (cache dynamique)
const CACHE_STRATEGIES = {
    // Cache First - pour les assets statiques
    cacheFirst: [
        /\.(?:png|jpg|jpeg|svg|gif|webp|ico)$/,
        /\.(?:woff|woff2|ttf|eot)$/,
        /fonts\.bunny\.net/,
        /cdnjs\.cloudflare\.com/,
        /cdn\.datatables\.net/,
        /cdn\.jsdelivr\.net/,
    ],
    // Network First - pour les pages HTML et API
    networkFirst: [
        /\/api\//,
        /\.html$/,
    ],
    // Stale While Revalidate - pour CSS/JS
    staleWhileRevalidate: [
        /\.(?:js|css)$/,
        /build\//,
    ]
};

// Installation du service worker
self.addEventListener('install', (event) => {
    console.log('[SW] Installation...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Pré-cache des ressources essentielles');
                return cache.addAll(PRECACHE_ASSETS.filter(url => !url.startsWith('http') || url.includes(location.host)));
            })
            .then(() => {
                // Fetch external resources separately (they might fail)
                return caches.open(CACHE_NAME).then(cache => {
                    PRECACHE_ASSETS
                        .filter(url => url.startsWith('http') && !url.includes(location.host))
                        .forEach(url => {
                            fetch(url, { mode: 'no-cors' })
                                .then(response => cache.put(url, response))
                                .catch(() => console.log('[SW] Cache externe échoué:', url));
                        });
                });
            })
            .then(() => self.skipWaiting())
    );
});

// Activation et nettoyage des anciens caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activation...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => name !== CACHE_NAME)
                        .map((name) => {
                            console.log('[SW] Suppression ancien cache:', name);
                            return caches.delete(name);
                        })
                );
            })
            .then(() => self.clients.claim())
    );
});

// Interception des requêtes
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Ignorer les requêtes non-GET
    if (request.method !== 'GET') {
        return;
    }
    
    // Ignorer certaines requêtes
    if (url.pathname.startsWith('/livewire/') || 
        url.pathname.startsWith('/_debugbar/') ||
        url.pathname.includes('hot-update')) {
        return;
    }

    // Déterminer la stratégie de cache
    const strategy = getStrategy(url);
    
    switch (strategy) {
        case 'cacheFirst':
            event.respondWith(cacheFirst(request));
            break;
        case 'networkFirst':
            event.respondWith(networkFirst(request));
            break;
        case 'staleWhileRevalidate':
            event.respondWith(staleWhileRevalidate(request));
            break;
        default:
            // Par défaut: Network First pour les pages
            if (request.mode === 'navigate') {
                event.respondWith(networkFirst(request));
            } else {
                event.respondWith(staleWhileRevalidate(request));
            }
    }
});

// Déterminer la stratégie selon l'URL
function getStrategy(url) {
    const pathname = url.pathname + url.search;
    
    for (const [strategy, patterns] of Object.entries(CACHE_STRATEGIES)) {
        for (const pattern of patterns) {
            if (pattern.test(pathname) || pattern.test(url.href)) {
                return strategy;
            }
        }
    }
    return null;
}

// Stratégie Cache First
async function cacheFirst(request) {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
        return cachedResponse;
    }
    
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.log('[SW] Cache First - Erreur réseau:', error);
        return new Response('Ressource non disponible hors ligne', { status: 503 });
    }
}

// Stratégie Network First
async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.log('[SW] Network First - Fallback cache');
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Pour les pages, retourner la page offline
        if (request.mode === 'navigate') {
            return caches.match(OFFLINE_URL);
        }
        
        return new Response('Non disponible hors ligne', { status: 503 });
    }
}

// Stratégie Stale While Revalidate
async function staleWhileRevalidate(request) {
    const cache = await caches.open(CACHE_NAME);
    const cachedResponse = await cache.match(request);
    
    const fetchPromise = fetch(request)
        .then((response) => {
            if (response.ok) {
                cache.put(request, response.clone());
            }
            return response;
        })
        .catch(() => cachedResponse);
    
    return cachedResponse || fetchPromise;
}

// Écouter les messages du client
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CLEAR_CACHE') {
        caches.delete(CACHE_NAME).then(() => {
            console.log('[SW] Cache vidé');
        });
    }
});

// Notification push (préparation pour plus tard)
self.addEventListener('push', (event) => {
    if (!event.data) return;
    
    const data = event.data.json();
    const options = {
        body: data.message || 'Nouvelle notification',
        icon: '/images/icons/icon-192x192.png',
        badge: '/images/icons/icon-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            url: data.url || '/',
            dateOfArrival: Date.now(),
        },
        actions: [
            { action: 'view', title: 'Voir' },
            { action: 'close', title: 'Fermer' }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title || 'BAOBAB Express', options)
    );
});

// Clic sur notification
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    if (event.action === 'close') return;
    
    const url = event.notification.data?.url || '/';
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                for (const client of clientList) {
                    if (client.url === url && 'focus' in client) {
                        return client.focus();
                    }
                }
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
    );
});

// Synchronisation en arrière-plan (préparation)
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-data') {
        console.log('[SW] Synchronisation des données...');
        // Implémenter la logique de sync ici
    }
});

console.log('[SW] Service Worker chargé - v1');
