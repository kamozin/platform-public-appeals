# Nuxt-админка для управления публичным контентом

## Context

Backend уже хранит публичные категории, новости и обращения в БД и отдаёт admin API под `/api/v1/admin/*`.
Публичные Nuxt-страницы берут данные через API, но для редактирования контента пока нет UI в админке.

## Goal

Добавить Nuxt-раздел `/admin`, через который admin-пользователь может управлять публичным контентом:

- категориями;
- новостями;
- публичными обращениями;
- вложениями, таймлайном, документами и официальным ответом обращения.

Раздел должен работать через существующий `useApi()` и bearer token, без хардкода `/api/v1`.

## Non-goals

- Не менять исходную папку `static/`.
- Не добавлять Laravel web UI, Blade, Inertia или Livewire.
- Не менять backend-контракт admin API, кроме явно необходимой мелкой корректировки типов.
- Не строить полноценную RBAC-модель ролей.
- Не добавлять загрузку файлов в хранилище; URL медиа вводятся как значения контента.

## Backend changes

Backend-изменения не планируются.
Используются существующие endpoints:

- `GET|POST /api/v1/admin/categories`
- `PATCH|DELETE /api/v1/admin/categories/{id}`
- `GET|POST /api/v1/admin/news`
- `PATCH|DELETE /api/v1/admin/news/{id}`
- `GET|POST /api/v1/admin/appeals`
- `PATCH|DELETE /api/v1/admin/appeals/{id}`

## Frontend changes

- Добавить типы admin DTO/payload в `frontend/app/types/api/admin-content.ts`.
- Добавить composable `frontend/app/composables/useAdminContent.ts`.
- Добавить страницу `frontend/app/pages/admin/index.vue`.
- Добавить routeRules для `/admin` и `/admin/**`: client-only и noindex.
- Добавить стили админки в `frontend/app/assets/styles/main.css`.
- Обновить `AuthUserDto`, чтобы frontend знал о `isAdmin`.

## API contract

Страница использует существующий JSON-контракт:

- список категорий возвращает `data.groups[]`;
- список новостей возвращает `data.items[]`;
- список обращений возвращает `data.items[]`;
- mutation endpoints возвращают обновлённую сущность в `data`;
- удаление возвращает `204 No Content`;
- ошибки соответствуют общему формату `error.code`.

## SEO impact

`/admin` и `/admin/**` не индексируются:

- SSR отключён;
- в `routeRules` выставляется заголовок `X-Robots-Tag: noindex, nofollow`;
- на странице применяется `useNoindexSeo()`.

Публичные SEO-страницы не меняются.

## Tests

Проверки:

- `docker compose exec -T nuxt pnpm typecheck`
- `docker compose exec -T laravel composer test`, если backend-контракт будет затронут.

## Acceptance criteria

- `/admin` требует авторизации.
- Пользователь без `isAdmin` не получает доступ к CRUD-интерфейсу.
- Admin может загрузить списки категорий, новостей и обращений из API.
- Admin может создать, обновить и удалить категорию.
- Admin может создать, обновить и удалить новость.
- Admin может создать, обновить и удалить обращение.
- Поля публичности/статуса управляются из формы.
- Вложения, таймлайн, документы и официальный ответ обращения управляются из формы.
- В коде frontend нет прямого хардкода `/api/v1`.
- TypeScript typecheck проходит.

## Postman

Новые endpoints не добавляются.
Существующая Postman collection уже содержит admin content endpoints, обновление не требуется.
