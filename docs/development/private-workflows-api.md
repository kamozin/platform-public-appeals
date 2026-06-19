# Приватные workflow API

Документ фиксирует реализацию задач 11-19: комментарии, auth, verification/password reset, черновики, вложения, профиль, dashboard, подписки и видео поддержки.

## Auth

Используется собственный Bearer token workflow:

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/2fa/verify`
- `GET /api/v1/auth/me`
- `POST /api/v1/auth/logout`

Токены хранятся в таблице `api_tokens` только в виде `sha256` hash.

Если у пользователя включена email-2FA, `POST /api/v1/auth/login` после проверки логина и пароля не возвращает bearer token. Вместо этого API создаёт challenge `two_factor_login` и возвращает `requiresTwoFactor`, `challengeId`, `expiresAt` и `maskedTarget`. Bearer token выдаётся только после успешного `POST /api/v1/auth/2fa/verify`.

## Verification и password reset

Коды подтверждения хранятся в `verification_challenges`:

- `POST /api/v1/auth/verification/send`
- `POST /api/v1/auth/verification/verify`
- `POST /api/v1/auth/password-reset/send`
- `POST /api/v1/auth/password-reset/verify`
- `POST /api/v1/auth/password-reset/complete`

Код подтверждения фиксированный: `123456` для всех окружений проекта. В local/testing API дополнительно возвращает `devCode = 123456`; в production код не должен возвращаться клиенту в JSON-ответе. Email-уведомление отправляется через брендированный HTML-шаблон `emails.auth.verification-code`.

Для email-2FA используются те же `verification_challenges`, но такие challenge привязаны к `user_id` и помечаются `consumed_at` после успешного использования. Проверка принадлежности challenge пользователю выполняется до проверки кода, поэтому чужой challenge не расходуется и не получает новые попытки. Повторное использование кода возвращает `CHALLENGE_ALREADY_CONSUMED`.

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

Чужие черновики не раскрываются и возвращают `NOT_FOUND`. После submit статус меняется на `pending_moderation`.

Статусы черновика:

- `draft` - черновик ещё не отправлен;
- `pending_moderation` - обращение отправлено и ждёт проверки;
- `needs_changes` - модератор запросил правки, пользователь может обновить данные и повторно отправить обращение;
- `rejected` - обращение отклонено;
- `approved` - обращение одобрено и связано с публичной карточкой.

Пользователь может редактировать `draft`, `pending_moderation` и `needs_changes`. Повторная отправка разрешена для `draft` и `needs_changes`. После повторной отправки `moderationNote` очищается, а история действий остаётся в admin-журнале модерации.

Черновик можно создать без авторизации. В этом случае `POST /api/v1/appeal-drafts` возвращает `data.guestToken`; клиент должен сохранить его и передавать в дальнейших запросах:

```txt
X-Appeal-Draft-Token: <guest-token>
```

Plain token возвращается только один раз при создании гостевого черновика. В базе хранится только `guest_token_hash`. Для авторизованного пользователя `guestToken` в ответе равен `null`, а доступ остаётся через `Authorization: Bearer <token>`.

DTO черновика дополнительно возвращает:

- `moderatedAt`;
- `moderationNote`;
- `rejectionReason`;
- `publicAppealId`.

## Profile и dashboard

Профиль:

- `GET /api/v1/profile`
- `PATCH /api/v1/profile`
- `PATCH /api/v1/profile/password`
- `POST /api/v1/profile/security/email-2fa/send`
- `POST /api/v1/profile/security/email-2fa/enable`
- `DELETE /api/v1/profile/security/email-2fa`
- `POST /api/v1/profile/avatar`
- `DELETE /api/v1/profile/avatar`

Смена пароля требует `current_password`, новый `password` и `password_confirmation`. После успешной смены пароля API оставляет текущий bearer token и отзывает остальные токены пользователя.

Включение email-2FA выполняется в два шага: сначала пользователь подтверждает текущий пароль и запрашивает код на текущий email, затем отправляет `challenge_id` и `code`. Отключение email-2FA требует текущий пароль. Challenge включения нельзя применить к другому пользователю или другому email.

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
