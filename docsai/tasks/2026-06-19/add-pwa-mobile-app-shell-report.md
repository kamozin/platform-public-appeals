# Отчёт: PWA shell мобильного приложения

## Что сделано

- Добавлен `@vite-pwa/nuxt` и PWA-конфиг в Nuxt.
- Добавлен manifest со `start_url: /app`, `display: standalone`, PWA-иконками и shortcuts.
- Добавлен отдельный layout `pwa.vue` без обычного header/footer сайта.
- Добавлены app-only экраны:
  - `/app`;
  - `/app/feed`;
  - `/app/new`;
  - `/app/profile`;
  - `/offline`.
- Добавлены PWA-компоненты:
  - app bar;
  - bottom navigation;
  - offline banner;
  - install prompt;
  - update prompt.
- Добавлены PNG/SVG assets для manifest и Apple Home Screen.
- Добавлен offline fallback: Workbox navigation fallback ведёт на prerender route `/offline`.
- Добавлена документация `docs/development/frontend-pwa.md`.

## Затронутые файлы

- `frontend/package.json`
- `frontend/pnpm-lock.yaml`
- `frontend/nuxt.config.ts`
- `frontend/app/app.vue`
- `frontend/app/layouts/pwa.vue`
- `frontend/app/components/pwa/*`
- `frontend/app/pages/app/*`
- `frontend/app/pages/offline.vue`
- `frontend/app/assets/styles/main.css`
- `frontend/public/offline.html`
- `frontend/public/pwa/*`
- `docs/development/frontend-pwa.md`

## Backend

Backend не изменялся.

Laravel остаётся API-only.

## API contract

Новые endpoints не добавлены.

PWA-экраны используют существующий `useApi()` и относительные API paths без прямого хардкода `/api/v1`.

## SEO

- `/app` и `/app/**` закрыты от индексации через route rules и `useNoindexSeo()`.
- `/offline` закрыт от индексации и prerender-ится для service worker fallback.
- Публичные SSR/SWR/prerender route rules не переводились в SPA.

## Проверки

Выполнено:

```bash
docker compose run --rm --no-deps nuxt sh -lc "corepack enable && pnpm typecheck"
docker compose run --rm --no-deps nuxt sh -lc "corepack enable && pnpm lint"
docker compose run --rm --no-deps nuxt sh -lc "corepack enable && pnpm build"
```

Результат:

- typecheck прошёл;
- lint прошёл;
- build прошёл;
- PWA build сгенерировал `sw.js` и Workbox runtime;
- precache: 64 entries, около 892 KiB;
- manifest в `.output/public/manifest.webmanifest` содержит `start_url: /app` и `display: standalone`.

Дополнительно проверено:

- в новых PWA app-файлах нет прямого `/api/v1`;
- `sw.js` содержит navigation fallback на `/offline`;
- большие изображения из `frontend/public/assets` не попадают в precache.
- локальный dev stack отвечает на `https://rukadobra.localhost/app` со статусом `200` и `X-Robots-Tag: noindex, nofollow`;
- `https://rukadobra.localhost/manifest.webmanifest` отвечает `200`;
- HTML `/app` содержит `<link rel="manifest" href="/manifest.webmanifest">`.

## Остаточные проверки

Нужны ручные проверки на устройствах:

- установка в Chrome Android;
- добавление на Home Screen в iOS Safari;
- Lighthouse PWA audit;
- offline navigation через DevTools Application/Network.

## Замечания

`pnpm peers check` показывает peer warning:

```txt
@eslint/js@10.0.1 хочет eslint ^10.0.0, проект использует eslint 9.x
```

`pnpm lint` и `pnpm build` при этом проходят. ESLint major upgrade не входит в эту PWA-задачу.

## Postman

Postman collection не обновлялась, потому что API endpoints и payload contract не менялись.
