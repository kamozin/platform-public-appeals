# Отчёт: Nuxt-админка для управления публичным контентом

## Что сделано

Добавлен frontend-раздел `/admin` для управления публичным контентом из БД через существующий Laravel admin API.

Реализовано:

- проверка авторизации через `useAuth().fetchMe()`;
- проверка `isAdmin`;
- загрузка списков категорий, новостей и обращений;
- создание, редактирование и удаление категорий;
- создание, редактирование и удаление новостей;
- создание, редактирование и удаление обращений;
- управление публичностью, статусом, счётчиками и датой публикации обращения;
- управление вложениями, таймлайном, документами и официальным ответом обращения;
- client-only routeRules для `/admin`;
- noindex через `X-Robots-Tag` и `useNoindexSeo()`;
- типизированный frontend API-layer без хардкода `/api/v1`.

## Затронутые файлы

- `frontend/app/pages/admin/index.vue`
- `frontend/app/composables/useAdminContent.ts`
- `frontend/app/types/api/admin-content.ts`
- `frontend/app/types/api/private.ts`
- `frontend/app/components/layout/AppIcon.vue`
- `frontend/app/components/layout/AppSvgSprite.vue`
- `frontend/app/assets/styles/main.css`
- `frontend/nuxt.config.ts`
- `docs/development/public-content-api.md`
- `docsai/tasks/2026-06-19/admin-content-nuxt-ui.md`

## Backend

Backend-контракт не менялся.
Используются уже существующие endpoints `/api/v1/admin/categories`, `/api/v1/admin/news`, `/api/v1/admin/appeals`.

## Postman

Обновление Postman collection не требуется, потому что новые endpoints не добавлялись.

## Проверки

Выполнено:

```bash
docker compose exec -T nuxt pnpm typecheck
docker compose exec -T nuxt pnpm lint
```

Результат: успешно.

Runtime smoke:

- `GET https://rukadobra.localhost/admin` через nginx возвращает `200 OK`;
- ответ содержит `X-Robots-Tag: noindex, nofollow`;
- `POST /api/v1/auth/login` для `admin@example.com` возвращает пользователя с `isAdmin = true`;
- `GET /api/v1/admin/categories` с bearer token возвращает `data.groups`.
