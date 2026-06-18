# Dashboard shell and profile

## Context

`static/dashboard.html` содержит личный кабинет с несколькими экранами. Первый атомарный шаг для dashboard должен дать shell, приватный доступ и профиль, включая avatar upload вместо `localStorage`.

## Goal

Реализовать `/dashboard` shell и настройки профиля.

## Non-goals

- Не переносить все разделы dashboard.
- Не реализовывать security sessions.
- Не реализовывать достижения.

## Backend changes

- Endpoint-ы:
  - `GET /api/v1/profile`;
  - `PATCH /api/v1/profile`;
  - `POST /api/v1/profile/avatar`;
  - `DELETE /api/v1/profile/avatar`.
- Валидация profile/avatar через FormRequest.
- Avatar max 2 МБ, JPG/PNG/WebP.

## Frontend changes

- Перенести dashboard layout/sidebar.
- Реализовать приватный route guard.
- Перенести screen `dashboard` с summary placeholder/API.
- Перенести screen `profile`.
- Avatar upload через API.

## API contract

Ошибки:
- `UNAUTHORIZED`;
- `VALIDATION_FAILED`;
- `AVATAR_TYPE_NOT_ALLOWED`;
- `AVATAR_TOO_LARGE`.

## SEO impact

- `/dashboard` noindex.
- Не попадать в sitemap.
- Не отдавать приватные данные в публичный SSR cache.

## Tests

- Backend feature tests profile get/update/avatar.
- Frontend private route smoke.

## Acceptance criteria

- [ ] Dashboard доступен только авторизованным.
- [ ] Profile данные читаются и обновляются.
- [ ] Avatar upload работает через API.
- [ ] Avatar не хранится в `localStorage`.
- [ ] Postman обновлен.

## Postman

- [ ] Добавить profile/avatar endpoints.

