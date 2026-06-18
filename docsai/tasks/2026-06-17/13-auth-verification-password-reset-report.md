# 13 Auth Verification Password Reset Report

## Что сделано

- Добавлены endpoints verification send/verify.
- Добавлены endpoints password reset send/verify/complete.
- Коды хранятся в `verification_challenges`.
- Добавлены Nuxt страницы `/verification` и `/password-reset`.

## Проверки

- Feature tests `VerificationEndpointTest`.
- Backend lint и Nuxt typecheck/lint.

## Postman

Добавлены verification и password reset endpoints.
