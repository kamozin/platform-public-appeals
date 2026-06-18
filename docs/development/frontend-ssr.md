# Frontend SSR foundation

Документ фиксирует базовую настройку Nuxt после задачи `03-frontend-ssr-foundation`.

## Стек

- Nuxt 4.4.x.
- Vue 3.5.x.
- TypeScript strict.
- Package manager: `pnpm` 11.7.0.
- SSR включен через `ssr: true`.

Frontend расположен в `frontend/`. Nuxt app source находится в `frontend/app/`.

## Runtime config

Настройки задаются через env:

```txt
NUXT_API_INTERNAL_BASE=http://nginx:8080/api/v1
NUXT_PUBLIC_API_BASE=/api/v1
NUXT_PUBLIC_SITE_URL=https://rukadobra.localhost
```

- `apiInternalBase` приватный и используется только на server-side Nuxt.
- `public.apiBase` доступен браузеру.
- `public.siteUrl` используется для canonical URL и SEO meta.

Внутренний `nginx:8080` нужен, чтобы SSR-запросы Nuxt ходили к Laravel API без публичного HTTPS redirect.

## API client

`frontend/app/composables/useApi.ts` предоставляет общий API client:

- на сервере использует `apiInternalBase`;
- в браузере использует `public.apiBase`;
- по умолчанию отправляет `Accept: application/json`;
- не хардкодит `/api/v1` в компонентах.

Пример:

```ts
const api = useApi();
const response = await api.get<ApiDataEnvelope<HealthStatusDto>>('/health');
```

## SEO helpers

`frontend/app/composables/usePageSeo.ts` содержит:

- `useCanonicalUrl`;
- `usePageSeo`;
- `useNoindexSeo`.

Базовая страница уже отдает SSR HTML с:

- `title`;
- `meta description`;
- canonical;
- `robots`;
- `h1`.

## Локальная разработка

```bash
docker compose up -d --build
```

Frontend доступен через общий HTTPS-домен:

```txt
https://rukadobra.localhost
```

## Проверки

```bash
docker compose run --rm nuxt sh -lc "corepack enable && corepack prepare pnpm@11.7.0 --activate && pnpm typecheck && pnpm lint && pnpm build"
curl -k https://rukadobra.localhost/
```

Важно: `typecheck`, `lint` и `build` лучше запускать последовательно, потому что Nuxt генерирует `.nuxt` types перед проверками.
