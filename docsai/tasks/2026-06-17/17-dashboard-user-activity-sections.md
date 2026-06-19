# Dashboard user activity sections

## Context

В dashboard есть разделы: мои обращения, черновики, сохраненные жалобы, мои комментарии. Они зависят от уже реализованных appeals/drafts/comments.

## Goal

Реализовать пользовательские activity-разделы dashboard.

## Non-goals

- Не реализовывать notifications/security/achievements.
- Не реализовывать модераторские действия.

## Backend changes

- Endpoint-ы:
  - `GET /api/v1/dashboard/appeals`;
  - `GET /api/v1/dashboard/drafts`;
  - `GET /api/v1/dashboard/saved-appeals`;
  - `POST /api/v1/appeals/{appeal}/save`;
  - `DELETE /api/v1/appeals/{appeal}/save`;
  - `GET /api/v1/dashboard/comments`.

## Frontend changes

- Перенести dashboard screens:
  - appeals;
  - drafts;
  - saved;
  - comments.
- Подключить данные через API.
- Синхронизировать вкладки с route/hash без потери SSR safety.

## API contract

Ошибки:
- `UNAUTHORIZED`;
- `NOT_FOUND`;
- `FORBIDDEN`.

## SEO impact

Все dashboard screens noindex.

## Tests

- Feature tests на приватные списки.
- Application tests ownership rules.

## Acceptance criteria

- [x] Пользователь видит только свои обращения.
- [x] Пользователь видит только свои черновики.
- [x] Saved appeals работают.
- [x] Comments section показывает комментарии пользователя.
- [x] Postman обновлен.

## Postman

- [x] Добавить dashboard activity endpoints.
