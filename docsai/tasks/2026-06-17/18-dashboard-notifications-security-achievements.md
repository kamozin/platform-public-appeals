# Dashboard notifications security achievements

## Context

В dashboard есть разделы уведомлений, истории статусов, безопасности, активных сеансов и достижений. Эти разделы лучше реализовывать после базового auth/profile и activity-разделов.

## Goal

Реализовать оставшиеся dashboard-разделы.

## Non-goals

- Не делать full 2FA, если она не реализована отдельной задачей.
- Не делать push notifications.
- Не делать публичный рейтинг.

## Backend changes

- Endpoint-ы:
  - `GET /api/v1/dashboard/notifications`;
  - `POST /api/v1/dashboard/notifications/mark-all-read`;
  - `PATCH /api/v1/dashboard/notification-settings`;
  - `GET /api/v1/dashboard/status-history`;
  - `GET /api/v1/dashboard/security/sessions`;
  - `DELETE /api/v1/dashboard/security/sessions/{id}`;
  - `GET /api/v1/dashboard/achievements`.

## Frontend changes

- Перенести screens:
  - notifications;
  - statuses;
  - security;
  - achievements.
- Подключить mark all read.
- Подключить notification settings.
- Подключить terminate session.

## API contract

Ошибки:
- `UNAUTHORIZED`;
- `NOT_FOUND`;
- `FORBIDDEN`;
- `CONFLICT`.

## SEO impact

Noindex, не sitemap.

## Tests

- Feature tests на все endpoint-ы.
- Application tests ownership/session rules.

## Acceptance criteria

- [x] Уведомления можно отметить прочитанными.
- [x] Настройки уведомлений сохраняются.
- [x] История статусов отображается.
- [x] Сессии можно завершать, кроме текущей по правилам безопасности.
- [x] Достижения отображаются.
- [x] Postman обновлен.

## Postman

- [x] Добавить dashboard notifications/security/achievements endpoints.
