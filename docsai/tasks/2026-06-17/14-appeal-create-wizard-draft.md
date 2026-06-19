# Appeal create wizard draft

## Context

`static/appeal.html` содержит 7-шаговый wizard подачи обращения. Чтобы задача была атомарной, первый backend шаг должен покрыть основные данные и черновики без файлов и SmartCaptcha.

## Goal

Реализовать создание и сохранение черновика обращения с основными полями.

## Non-goals

- Не реализовывать загрузку файлов.
- Не реализовывать SmartCaptcha.
- Не публиковать обращение.
- Не реализовывать модерацию.

## Backend changes

- Миграции для appeals/drafts или единая таблица appeals со статусом `draft`.
- Endpoint-ы:
  - `POST /api/v1/appeal-drafts`;
  - `PATCH /api/v1/appeal-drafts/{id}`;
  - `GET /api/v1/appeal-drafts/{id}`;
  - `DELETE /api/v1/appeal-drafts/{id}`.
- Поля:
  - category;
  - submission type;
  - title;
  - description;
  - urgency;
  - location;
  - contact visibility;
  - contact data.

## Frontend changes

- Перенести wizard базовых шагов.
- Состояние формы держать в typed view model.
- Сохранять черновик через API.
- Поддержать восстановление черновика.

## API contract

Основные ошибки:
- `VALIDATION_FAILED`;
- `UNAUTHORIZED`;
- `NOT_FOUND`;
- `FORBIDDEN`.

## SEO impact

- `/appeal/new` noindex.
- Не попадать в sitemap.

## Tests

- Application tests на create/update draft.
- Feature tests CRUD draft.
- Frontend smoke wizard navigation.

## Acceptance criteria

- [x] Авторизованный пользователь может создать черновик.
- [x] Черновик можно обновить.
- [x] Чужой черновик недоступен.
- [x] Wizard не теряет данные между шагами.
- [x] Postman обновлен.

## Postman

- [x] Добавить draft endpoints.
