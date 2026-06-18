# Auth register login me

## Context

`static/login.html` и `static/register.html` содержат вход и регистрацию. Сейчас login просто перенаправляет на `dashboard.html`, реальной авторизации нет.

## Goal

Реализовать базовую пользовательскую регистрацию, login/logout и `me`.

## Non-goals

- Не реализовывать email/phone verification.
- Не реализовывать password reset.
- Не реализовывать 2FA.

## Backend changes

- Создать пользователей.
- Добавить auth через Laravel Sanctum или выбранный API-token подход.
- Endpoint-ы:
  - `POST /api/v1/auth/register`;
  - `POST /api/v1/auth/login`;
  - `GET /api/v1/auth/me`;
  - `POST /api/v1/auth/logout`.
- Валидация через FormRequest.
- DTO/use-cases для register/login.

## Frontend changes

- Перенести login/register pages в Nuxt.
- Подключить формы к API.
- Хранить auth state безопасно для SSR.
- После успешного входа вести в `/dashboard`.

## API contract

Register request:

```json
{
  "name": "Иван Иванов",
  "phone": "+79001234567",
  "email": "ivan@example.com",
  "password": "secret",
  "password_confirmation": "secret",
  "privacy": true,
  "notifications": true
}
```

Login request:

```json
{
  "login": "ivan@example.com",
  "password": "secret",
  "remember": true
}
```

Errors:
- `VALIDATION_FAILED`;
- `INVALID_CREDENTIALS`;
- `UNAUTHORIZED`.

## SEO impact

- `/login` и `/register` должны быть `noindex,nofollow`.
- Не попадать в sitemap.

## Tests

- Backend feature tests register/login/me/logout.
- Application tests для register/login branches.
- Frontend form smoke, если тестовый стек готов.

## Acceptance criteria

- [ ] Регистрация создает пользователя.
- [ ] Login возвращает auth result.
- [ ] `me` работает только для авторизованного пользователя.
- [ ] Logout завершает сессию/token.
- [ ] Auth pages noindex.
- [ ] Postman обновлен.

## Postman

- [ ] Добавить register/login/me/logout.
- [ ] Добавить примеры ошибок.

