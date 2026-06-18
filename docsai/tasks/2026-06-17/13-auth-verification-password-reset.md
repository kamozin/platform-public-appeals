# Auth verification and password reset

## Context

`static/verification.html` и `static/password-reset.html` показывают подтверждение телефона/email и восстановление пароля. Для локальной почты используется Mailpit.

## Goal

Реализовать подтверждение email/phone кодом и восстановление пароля.

## Non-goals

- Не реализовывать SMS provider в production.
- Не реализовывать 2FA.
- Не менять базовую регистрацию/login из предыдущей задачи.

## Backend changes

- Таблица verification/password reset challenges.
- Endpoint-ы:
  - `POST /api/v1/auth/verification/send`;
  - `POST /api/v1/auth/verification/verify`;
  - `POST /api/v1/auth/password-reset/send`;
  - `POST /api/v1/auth/password-reset/verify`;
  - `POST /api/v1/auth/password-reset/complete`.
- Email отправлять через Laravel mailer в Mailpit на локали.
- Phone challenge пока реализовать через dev stub или отдельный интерфейс без внешнего SMS.

## Frontend changes

- Перенести verification/password reset pages.
- Реализовать OTP input.
- Подключить resend flow.
- Подключить reset flow из трех шагов.

## API contract

Коды ошибок:
- `VALIDATION_FAILED`;
- `CHALLENGE_NOT_FOUND`;
- `CHALLENGE_EXPIRED`;
- `INVALID_VERIFICATION_CODE`;
- `TOO_MANY_ATTEMPTS`.

## SEO impact

- `/verification` и `/password-reset` noindex.
- Не попадать в sitemap.

## Tests

- Application tests на send/verify/expired/attempts.
- Feature tests endpoint-ов.
- Mail fake/Mailpit локально не использовать в unit tests напрямую.

## Acceptance criteria

- [ ] Email-код отправляется и виден в Mailpit локально.
- [ ] Неверный код возвращает стабильную ошибку.
- [ ] Истекший код возвращает стабильную ошибку.
- [ ] Password reset меняет пароль после валидного challenge.
- [ ] Postman обновлен.

## Postman

- [ ] Добавить verification и password reset endpoints.
- [ ] Добавить примеры ошибок.

