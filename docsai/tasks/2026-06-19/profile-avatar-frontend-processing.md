# Обработка аватарки профиля на frontend

## Context

В личном кабинете уже есть backend-контракт для аватарки профиля:

- `POST /api/v1/profile/avatar`;
- `DELETE /api/v1/profile/avatar`;
- поле `avatarUrl` в `AuthUserDto`.

Страница `/dashboard` отображает аватар, но не даёт пользователю загрузить или удалить изображение. Нужно добавить обработку изображения на стороне frontend перед отправкой в API.

## Goal

Добавить в профиль личного кабинета загрузку аватарки с frontend-обработкой:

- выбор JPG/PNG/WebP файла;
- проверка типа и размера до отправки;
- квадратное кадрирование по центру;
- resize до фиксированного размера;
- конвертация в web-friendly image blob;
- preview после обработки;
- отправка результата в `POST /profile/avatar`;
- удаление через `DELETE /profile/avatar`.

## Non-goals

- Не менять структуру хранения файлов на backend.
- Не добавлять отдельный cropper UI.
- Не менять auth flow.
- Не переносить обработку аватарки на backend.

## Backend changes

Backend не меняется. Используются существующие endpoint-ы профиля.

## Frontend changes

- Обновить `frontend/app/pages/dashboard.vue`.
- Использовать существующий `useApi()` и `useAuth()`.
- Обрабатывать изображение через browser canvas API.
- Не хранить аватарку в `localStorage`.

## API contract

Используются существующие endpoint-ы:

```txt
POST /api/v1/profile/avatar
Content-Type: multipart/form-data
field: avatar
201 { "data": AuthUserDto }

DELETE /api/v1/profile/avatar
204
```

Ошибки:

- `UNAUTHORIZED`;
- `VALIDATION_FAILED`;
- `AVATAR_TYPE_NOT_ALLOWED`;
- `AVATAR_TOO_LARGE`.

## SEO impact

Нет влияния. `/dashboard` остаётся приватной noindex-страницей.

## Tests

- `pnpm typecheck` во frontend.
- `pnpm lint` во frontend.

## Acceptance criteria

- [x] Пользователь может выбрать аватарку в профиле.
- [x] Frontend до отправки проверяет формат и размер.
- [x] Frontend создаёт квадратную обработанную версию изображения.
- [x] После upload обновляется `profile.avatarUrl` и `auth.user`.
- [x] Пользователь может удалить аватарку.
- [x] Ошибки показываются рядом с блоком аватарки.

## Postman

- [x] Проверить наличие profile/avatar endpoint-ов в коллекции.
- [x] Добавить недостающие запросы, если их нет.
