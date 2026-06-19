# Смена пароля и email-2FA в личном кабинете

## Context

В проекте уже есть регистрация, вход, `GET /api/v1/auth/me`, bearer-токены в `api_tokens`, профиль пользователя и вкладка безопасности кабинета с активными сессиями. Также уже есть таблица `verification_challenges` и email-коды для подтверждения контакта и восстановления пароля.

Нужно добавить в личный кабинет смену пароля и email-2FA: пользователь включает 2FA через код на почту, а при следующих входах после проверки логина и пароля должен дополнительно подтвердить вход email-кодом.

## Goal

- Дать авторизованному пользователю возможность сменить пароль в кабинете.
- Дать авторизованному пользователю возможность включить email-2FA через код на текущий email.
- Изменить login flow так, чтобы для пользователей с включенной email-2FA основной bearer token выдавался только после успешной проверки второго фактора.
- Показать управление паролем и email-2FA во вкладке безопасности `/dashboard`.

## Non-goals

- Не добавлять TOTP, QR-коды, backup-коды и WebAuthn.
- Не менять публичный SEO-фронт.
- Не переводить auth на Laravel Sanctum или session auth.
- Не менять исходную папку `static/`.
- Не делать отдельную страницу настроек аккаунта вне существующего кабинета.
- Не делать смену email в этой задаче.

## Backend changes

- Добавить миграцию для `users`:
  - `email_two_factor_enabled` boolean default false;
  - `email_two_factor_confirmed_at` nullable timestamp.
- Расширить `verification_challenges` для 2FA, если потребуется:
  - nullable `user_id` с индексом;
  - nullable `consumed_at`;
  - индекс по `purpose`, `target`, `expires_at`.
- Добавить security endpoints:
  - `PATCH /api/v1/profile/password`;
  - `POST /api/v1/profile/security/email-2fa/send`;
  - `POST /api/v1/profile/security/email-2fa/enable`;
  - `DELETE /api/v1/profile/security/email-2fa`.
- Добавить auth endpoint:
  - `POST /api/v1/auth/2fa/verify`.
- Обновить `AuthService`:
  - обычный login без 2FA возвращает прежний auth payload;
  - login с включенной 2FA создаёт challenge `two_factor_login` и возвращает ответ без bearer token;
  - verify 2FA выдаёт bearer token и помечает challenge consumed.
- Добавить application/service слой для безопасности профиля:
  - смена пароля с проверкой текущего пароля;
  - отправка кода включения email-2FA;
  - включение email-2FA только после валидного challenge;
  - отключение email-2FA с проверкой текущего пароля.
- После смены пароля удалить все остальные `api_tokens`, кроме текущего, если текущий токен определён.
- Обновить `userPayload`/profile DTO:
  - `emailTwoFactorEnabled: boolean`.

## Frontend changes

- Обновить `AuthUserDto` и auth composable под новое поле `emailTwoFactorEnabled`.
- Изменить `useAuth.login()` на поддержку двух вариантов ответа:
  - auth success с token;
  - `requiresTwoFactor` с `challengeId`, `expiresAt`, `maskedTarget`.
- Обновить `/login`:
  - после логина с включенной 2FA показывать шаг ввода email-кода;
  - после успешной проверки кода сохранять bearer token и вести в `/dashboard`;
  - не показывать токен или состояние авторизации до прохождения 2FA.
- Обновить вкладку `security` в `/dashboard`:
  - форма смены пароля: текущий пароль, новый пароль, подтверждение;
  - блок email-2FA: статус, отправка кода, ввод кода, включение;
  - отключение email-2FA через текущий пароль;
  - сообщения успеха/ошибки без раскрытия лишних деталей.

## API contract

### `PATCH /api/v1/profile/password`

Auth: bearer token.

Request:

```json
{
  "current_password": "old-password",
  "password": "new-password",
  "password_confirmation": "new-password"
}
```

Response `200`:

```json
{
  "data": {
    "changed": true
  }
}
```

### `POST /api/v1/profile/security/email-2fa/send`

Auth: bearer token.

Request:

```json
{
  "current_password": "current-password"
}
```

Response `201`:

```json
{
  "data": {
    "id": "uuid",
    "expiresAt": "2026-06-19T12:00:00+00:00",
    "maskedTarget": "i***@example.com"
  }
}
```

### `POST /api/v1/profile/security/email-2fa/enable`

Auth: bearer token.

Request:

```json
{
  "challenge_id": "uuid",
  "code": "123456"
}
```

Response `200`:

```json
{
  "data": {
    "emailTwoFactorEnabled": true
  }
}
```

### `DELETE /api/v1/profile/security/email-2fa`

Auth: bearer token.

Request:

```json
{
  "current_password": "current-password"
}
```

Response `200`:

```json
{
  "data": {
    "emailTwoFactorEnabled": false
  }
}
```

### `POST /api/v1/auth/login`

Для пользователей без email-2FA ответ остаётся совместимым:

```json
{
  "data": {
    "token": "plain-token",
    "tokenType": "Bearer",
    "user": {}
  }
}
```

Для пользователей с email-2FA:

```json
{
  "data": {
    "requiresTwoFactor": true,
    "challengeId": "uuid",
    "expiresAt": "2026-06-19T12:00:00+00:00",
    "maskedTarget": "i***@example.com"
  }
}
```

### `POST /api/v1/auth/2fa/verify`

Request:

```json
{
  "challenge_id": "uuid",
  "code": "123456"
}
```

Response `200`:

```json
{
  "data": {
    "token": "plain-token",
    "tokenType": "Bearer",
    "user": {}
  }
}
```

Ошибки:

- `UNAUTHORIZED`;
- `INVALID_CREDENTIALS`;
- `CURRENT_PASSWORD_INVALID`;
- `CHALLENGE_NOT_FOUND`;
- `CHALLENGE_EXPIRED`;
- `CHALLENGE_ALREADY_CONSUMED`;
- `INVALID_VERIFICATION_CODE`;
- `TOO_MANY_ATTEMPTS`;
- `EMAIL_TWO_FACTOR_ALREADY_ENABLED`;
- `EMAIL_TWO_FACTOR_NOT_ENABLED`.

## SEO impact

- `/login` и `/dashboard` остаются `noindex`.
- Sitemap, robots, публичные meta и SSR-страницы не меняются.

## Tests

- Feature tests:
  - смена пароля требует bearer token;
  - смена пароля отклоняет неверный текущий пароль;
  - смена пароля обновляет hash и удаляет остальные токены;
  - отправка кода email-2FA требует текущий пароль;
  - включение email-2FA требует валидный challenge;
  - login пользователя с включенной 2FA не выдаёт bearer token до второго фактора;
  - verify 2FA выдаёт bearer token;
  - повторное использование consumed challenge отклоняется;
  - отключение 2FA требует текущий пароль.
- Frontend checks:
  - `/login` показывает второй шаг при `requiresTwoFactor`;
  - `/dashboard` security tab показывает формы смены пароля и email-2FA.
- Static analysis / lint:
  - `php artisan test`;
  - `vendor/bin/phpstan analyse`;
  - `pnpm --dir frontend lint`, если frontend lint доступен.

## Acceptance criteria

- [x] Пользователь может сменить пароль в кабинете, указав текущий пароль.
- [x] После смены пароля старый пароль больше не подходит.
- [x] Остальные активные токены пользователя отзываются после смены пароля.
- [x] Пользователь может запросить email-код и включить email-2FA.
- [x] Пользователь с включенной email-2FA после логина/пароля получает 2FA challenge, а не bearer token.
- [x] После валидного email-кода пользователь получает bearer token и попадает в кабинет.
- [x] Неверный/истёкший/повторно использованный код возвращает единый API error contract.
- [x] В кабинете видно текущее состояние email-2FA.
- [x] Документация на русском обновлена.
- [x] Postman collection обновлена.

## Postman

Обновить `docsai/postmancollection/rukadobra-api.postman_collection.json`:

- [x] добавить `Profile / Change Password`;
- [x] добавить `Profile / Send Email 2FA Enable Code`;
- [x] добавить `Profile / Enable Email 2FA`;
- [x] добавить `Profile / Disable Email 2FA`;
- [x] добавить `Auth / Verify 2FA`;
- [x] добавить пример login response с `requiresTwoFactor`.
