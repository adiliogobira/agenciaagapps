/*
 * Service worker do Diaconia (PWA).
 *
 * Estratégia pensada para um app multi-tenant com dados privados:
 *  - Assets estáticos (CSS/JS/ícones/CDN): cache-first (rápido e offline).
 *  - Navegações (páginas HTML): network-first; se falhar, mostra offline.html.
 *    NÃO guardamos o HTML de páginas autenticadas em cache, para não vazar
 *    dados entre usuários que compartilhem o mesmo aparelho.
 *  - POST e chamadas de API nunca são cacheadas.
 */
const VERSION = 'diaconia-v1';
const STATIC_CACHE = `${VERSION}-static`;
const PRECACHE = [
  '/offline.html',
  '/assets/css/app.css',
  '/assets/icons/icon-192.png',
  '/assets/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => !k.startsWith(VERSION)).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;

  // Só tratamos GET; POST/PUT/DELETE vão direto para a rede.
  if (req.method !== 'GET') return;

  const url = new URL(req.url);

  // Nunca cachear a API.
  if (url.pathname.startsWith('/api/')) return;

  // Navegações (páginas): network-first com fallback offline.
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).catch(() => caches.match('/offline.html'))
    );
    return;
  }

  // Assets estáticos: cache-first, atualizando o cache em segundo plano.
  const isStatic = url.pathname.startsWith('/assets/') ||
                   url.origin.includes('cdn.jsdelivr.net') ||
                   /\.(css|js|png|jpg|jpeg|svg|webp|woff2?)$/.test(url.pathname);

  if (isStatic) {
    event.respondWith(
      caches.match(req).then((cached) => {
        const network = fetch(req).then((res) => {
          if (res && res.status === 200) {
            const copy = res.clone();
            caches.open(STATIC_CACHE).then((c) => c.put(req, copy));
          }
          return res;
        }).catch(() => cached);
        return cached || network;
      })
    );
  }
});
