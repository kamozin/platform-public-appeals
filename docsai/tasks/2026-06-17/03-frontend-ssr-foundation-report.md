# Отчет по задаче 03: Frontend SSR foundation

## Что сделано

- Инициализирован Nuxt 4 frontend в `frontend/`.
- Зафиксирован package manager `pnpm@11.7.0` и создан `pnpm-lock.yaml`.
- Включен SSR и строгий TypeScript.
- Добавлен `runtimeConfig`:
  - private `apiInternalBase`;
  - public `apiBase`;
  - public `siteUrl`.
- Добавлен общий SSR-safe API client `useApi`.
- Добавлены SEO helpers:
  - `useCanonicalUrl`;
  - `usePageSeo`;
  - `useNoindexSeo`.
- Добавлен default layout с базовыми `header/main/footer`.
- Добавлена стартовая SSR page `/` со smoke-запросом к Laravel `GET /api/v1/health`.
- Добавлен внутренний nginx listener `8080` для Nuxt SSR -> Laravel API.
- Обновлена Docker-документация: `docs/development/docker-local.md`.
- Создана frontend-документация: `docs/development/frontend-ssr.md`.

## Затронутые файлы

- `frontend/package.json`
- `frontend/pnpm-lock.yaml`
- `frontend/pnpm-workspace.yaml`
- `frontend/nuxt.config.ts`
- `frontend/tsconfig.json`
- `frontend/eslint.config.mjs`
- `frontend/app/app.vue`
- `frontend/app/layouts/default.vue`
- `frontend/app/pages/index.vue`
- `frontend/app/composables/useApi.ts`
- `frontend/app/composables/usePageSeo.ts`
- `frontend/app/types/api/common.ts`
- `frontend/app/types/seo.ts`
- `frontend/app/assets/styles/main.css`
- `docker/nginx/default.conf`
- `docker-compose.yml`
- `.env.example`
- `.gitignore`

## Проверки

- `docker compose config --quiet` — прошел.
- `docker compose run --rm nuxt sh -lc "corepack enable && corepack prepare pnpm@11.7.0 --activate && pnpm typecheck && pnpm lint && pnpm build"` — прошел.
- `curl -k https://rukadobra.localhost/api/v1/health` — `200`.
- `curl -k https://rukadobra.localhost/` — `200`, HTML отдан Nuxt SSR.
- SSR HTML содержит `h1`, `title`, `meta description`, canonical и `data-ssr="true"`.
- SSR health smoke показывает `API ok`.

## Postman

Postman collection не обновлялась, потому что в задаче не добавлялись новые backend API endpoints.

## Примечания

- `static/` не изменялась.
- При параллельном запуске `pnpm typecheck` и `pnpm build` возможна гонка за `.nuxt` generated types. Проверки нужно запускать последовательно.
- Следующая логическая задача: `04-frontend-static-assets-and-layout`.
