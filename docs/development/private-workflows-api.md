# Приватные workflow API

Документ фиксирует реализацию задач 11-19: комментарии, auth, verification/password reset, черновики, вложения, профиль, dashboard, подписки и видео поддержки.

## Auth

Используется собственный Bearer token workflow:

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `GET /api/v1/auth/me`
- `POST /api/v1/auth/logout`

Токены хранятся в таблице `api_tokens` только в виде `sha256` hash.

## Verification и password reset

Коды подтверждения хранятся в `verification_challenges`:

- `POST /api/v1/auth/verification/send`
- `POST /api/v1/auth/verification/verify`
- `POST /api/v1/auth/password-reset/send`
- `POST /api/v1/auth/password-reset/verify`
- `POST /api/v1/auth/password-reset/complete`

В local/testing API возвращает `devCode = 123456`. В production код не должен возвращаться клиенту.

## Комментарии

- `GET /api/v1/appeals/{appeal}/comments`
- `POST /api/v1/appeals/{appeal}/comments`

Публичный read-model остаётся доступен без авторизации. Создание комментария требует Bearer token и создаёт запись со статусом `pending`.

## Черновики обращений

- `POST /api/v1/appeal-drafts`
- `GET /api/v1/appeal-drafts/{id}`
- `PATCH /api/v1/appeal-drafts/{id}`
- `DELETE /api/v1/appeal-drafts/{id}`
- `POST /api/v1/appeal-drafts/{id}/attachments`
- `DELETE /api/v1/appeal-drafts/{id}/attachments/{attachment}`
- `POST /api/v1/appeal-drafts/{id}/submit`

Чужие черновики не раскрываются и возвращают `NOT_FOUND`. После submit статус меняется на `pending_moderation`, дальнейшее редактирование возвращает `CONFLICT`.

Черновик можно создать без авторизации. В этом случае `POST /api/v1/appeal-drafts` возвращает `data.guestToken`; клиент должен сохранить его и передавать в дальнейших запросах:

```txt
X-Appeal-Draft-Token: <guest-token>
```

Plain token возвращается только один раз при создании гостевого черновика. В базе хранится только `guest_token_hash`. Для авторизованного пользователя `guestToken` в ответе равен `null`, а доступ остаётся через `Authorization: Bearer <token>`.

## Profile и dashboard

Профиль:

- `GET /api/v1/profile`
- `PATCH /api/v1/profile`
- `POST /api/v1/profile/avatar`
- `DELETE /api/v1/profile/avatar`

Dashboard:

- `GET /api/v1/dashboard/appeals`
- `GET /api/v1/dashboard/drafts`
- `GET /api/v1/dashboard/saved-appeals`
- `GET /api/v1/dashboard/comments`
- `GET /api/v1/dashboard/notifications`
- `POST /api/v1/dashboard/notifications/mark-all-read`
- `PATCH /api/v1/dashboard/notification-settings`
- `GET /api/v1/dashboard/status-history`
- `GET /api/v1/dashboard/security/sessions`
- `DELETE /api/v1/dashboard/security/sessions/{id}`
- `GET /api/v1/dashboard/achievements`

## Подписки и видео поддержки

- `POST /api/v1/subscriptions/news`
- `DELETE /api/v1/subscriptions/news`
- `POST /api/v1/support-videos`

Подписка идемпотентна. Видео принимает MP4/MOV до 100 МБ и сохраняется со статусом `pending_moderation`.

## Проверки

DB feature-тесты нужно запускать внутри контейнера Laravel:

```bash
docker compose exec -T laravel composer test
docker compose exec -T laravel composer lint
```

Локальный PHP в WSL может не иметь `pdo_sqlite`, поэтому контейнерный запуск является основным для задач с БД.
