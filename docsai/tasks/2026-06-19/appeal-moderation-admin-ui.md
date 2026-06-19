# Nuxt UI модерации обращений

## Context

После backend-задачи `appeal-moderation-backend.md` появится admin API для очереди модерации обращений.
Сейчас `/admin/appeals` управляет уже публичными карточками `public_appeals`, но не показывает пользовательские draft-ы, которые ждут проверки.

## Goal

Добавить в Nuxt-админку отдельный раздел модерации обращений:

- список обращений на проверке;
- фильтры по moderation status;
- detail-панель обращения;
- просмотр контактов заявителя только для admin;
- metadata вложений;
- действия: запросить правки, отклонить, одобрить и создать публичную карточку;
- prefill формы публикации из draft-а.

## Non-goals

- Не менять backend-контракт, кроме точечной синхронизации типов с уже принятой backend-спекой.
- Не делать публичную страницу draft-а.
- Не делать отдельный файловый viewer для приватных вложений.
- Не добавлять SPA-only публичные SEO-страницы.
- Не править `static/`.
- Не менять визуальную структуру публичных страниц.

## Backend changes

Backend-изменения не планируются, кроме использования endpoints из `appeal-moderation-backend.md`.

## Frontend changes

- Расширить `frontend/app/types/api/admin-content.ts` типами moderation DTO/payload.
- Расширить `frontend/app/composables/useAdminContent.ts` методами:
  - `listModerationDrafts`;
  - `getModerationDraft`;
  - `requestAppealChanges`;
  - `rejectAppealDraft`;
  - `approveAppealDraft`.
- Добавить вкладку `/admin/moderation` в текущую админку.
- Разделить смысл:
  - `/admin/moderation` - входящие обращения от пользователей;
  - `/admin/appeals` - уже созданные публичные карточки.
- Добавить компактную list/detail раскладку:
  - слева очередь;
  - справа данные draft-а и действия модератора.
- При approve показывать форму публичной карточки:
  - slug;
  - title;
  - excerpt;
  - description;
  - status/statusLabel;
  - city/district/category/location;
  - imageUrl;
  - isPublic;
  - timeline/documents/officialResponse при необходимости.
- Не хардкодить `/api/v1`, использовать только `useApi()`.
- Сохранить `/admin/**` как client-only/noindex route.

## API contract

Используются endpoints из backend-спеки:

- `GET /api/v1/admin/appeal-moderation`
- `GET /api/v1/admin/appeal-moderation/{id}`
- `POST /api/v1/admin/appeal-moderation/{id}/request-changes`
- `POST /api/v1/admin/appeal-moderation/{id}/reject`
- `POST /api/v1/admin/appeal-moderation/{id}/approve`

Frontend должен обрабатывать:

- `UNAUTHORIZED`;
- `FORBIDDEN`;
- `NOT_FOUND`;
- `CONFLICT`;
- `VALIDATION_FAILED`.

## SEO impact

`/admin/moderation` и весь `/admin/**` остаются неиндексируемыми:

- `ssr: false`;
- `robots: false`;
- `useNoindexSeo()` в админке.

Публичные SEO-страницы меняются только косвенно: после approve появляется новая публичная карточка обращения.

## Tests

- `docker compose exec -T nuxt pnpm typecheck`.
- Если есть frontend test runner, добавить/обновить тесты для composable payload mapping.
- После backend-синхронизации выполнить `docker compose exec -T laravel composer test`.

## Acceptance criteria

- [ ] В админке есть отдельная вкладка `Модерация`.
- [ ] Admin видит очередь обращений по статусам `pending_moderation`, `needs_changes`, `rejected`, `approved`.
- [ ] Пользователь без admin-доступа не видит содержимое раздела.
- [ ] Detail-панель показывает исходные данные обращения, контакты и metadata вложений.
- [ ] Admin может отправить запрос правок с сообщением.
- [ ] Admin может отклонить обращение с причиной.
- [ ] Admin может одобрить обращение через форму публичной карточки.
- [ ] После approve список обновляется, а публичную карточку можно открыть по `/appeals/{slug}`.
- [ ] В коде нет прямого хардкода `/api/v1`.
- [ ] TypeScript typecheck проходит.

## Postman

Новые Postman-запросы добавляются в backend-задаче.
В этой frontend-задаче Postman не обновляется, если контракт backend не меняется.
