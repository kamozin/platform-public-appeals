# PWA-версия frontend

## Назначение

PWA-версия добавляет installable mobile app shell для Nuxt frontend без изменения Laravel backend.

Laravel остаётся API-only. Публичные SEO-страницы остаются SSR/SWR/prerender и не превращаются в SPA.

## Entry points

- `/app` - стартовый экран установленного приложения.
- `/app/feed` - app-like лента обращений.
- `/app/new` - app-like экран входа в сценарий подачи обращения.
- `/app/profile` - app-like профиль и вход в приватные разделы.
- `/offline` - prerender fallback для service worker.

Manifest использует:

- `start_url: /app`;
- `display: standalone`;
- `scope: /`;
- `orientation: portrait-primary`.

## Layout

PWA использует отдельный layout:

```txt
frontend/app/layouts/pwa.vue
```

В этом layout нет обычных `LayoutAppHeader` и `LayoutAppFooter`.

Состав shell-а:

- `PwaAppBar`;
- `PwaBottomNavigation`;
- `PwaOfflineBanner`;
- `PwaInstallPrompt`;
- `PwaUpdatePrompt`.

CSS-правила PWA находятся в `frontend/app/assets/styles/main.css` и используют:

- `100dvh`;
- `env(safe-area-inset-*)`;
- fixed bottom navigation;
- `@media (display-mode: standalone)`;
- touch-targets не меньше 44px.

## PWA config

PWA подключена через `@vite-pwa/nuxt` в `frontend/nuxt.config.ts`.

Workbox precache ограничен shell-файлами, manifest, PWA-иконками, CSS/JS/HTML и `/offline`.
Большие изображения из `frontend/public/assets` не precache-ятся, а кешируются runtime-стратегией `CacheFirst` при запросе.

Navigation fallback:

```txt
/offline
```

API-запросы не кешируются service worker-ом как offline source of truth.

## SEO

Маршруты `/app`, `/app/**` и `/offline` закрыты от индексации через `X-Robots-Tag: noindex, nofollow` и `useNoindexSeo()`.

PWA routes не должны добавляться в sitemap.

## Assets

PWA assets лежат в:

```txt
frontend/public/pwa/
```

Файлы:

- `icon.svg`;
- `icon-192.png`;
- `icon-512.png`;
- `maskable-192.png`;
- `maskable-512.png`;
- `apple-touch-icon.png`.

## Проверки

Минимальные проверки после изменений:

```bash
docker compose run --rm --no-deps nuxt sh -lc "corepack enable && pnpm typecheck"
docker compose run --rm --no-deps nuxt sh -lc "corepack enable && pnpm lint"
docker compose run --rm --no-deps nuxt sh -lc "corepack enable && pnpm build"
```

Ручные проверки:

- Chrome DevTools Application: manifest, service worker, cache storage;
- Lighthouse PWA audit;
- установка в Chrome Android;
- добавление на Home Screen в iOS Safari;
- запуск установленной PWA открывает `/app`;
- offline navigation показывает `/offline`.

## Ограничения MVP

В этом MVP не реализованы:

- push-уведомления;
- offline-черновики обращений;
- background sync;
- перенос полного `/appeal/new` wizard в PWA layout;
- native wrapper через Capacitor.
