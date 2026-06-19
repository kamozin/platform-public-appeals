# Исправить битую иллюстрацию восстановления пароля

## Context

На странице `/password-reset` Nuxt использует иллюстрацию `/assets/password-reset-illustration.png`.
Файл в `frontend/public/assets/password-reset-illustration.png` существовал, но не декодировался как PNG. Исходный файл в `static/assets/password-reset-illustration.png` валиден и используется как read-only источник переноса.

## Goal

Заменить поврежденную frontend-копию иллюстрации восстановления пароля на валидный PNG из `static/assets`.

## Non-goals

- Не менять backend API.
- Не менять форму восстановления пароля и ее API-вызовы.
- Не менять `static/`.
- Не менять общую верстку auth-страниц.

## Backend changes

Backend не меняется.

## Frontend changes

- Заменить `frontend/public/assets/password-reset-illustration.png` валидной копией из `static/assets/password-reset-illustration.png`.

## API contract

API-контракт не меняется.

## SEO impact

SEO не меняется. Страница `/password-reset` остается auth-экраном с текущими noindex-настройками.

## Tests

- Проверить, что frontend PNG декодируется как изображение.
- Сравнить SHA256 frontend-копии и исходного файла из `static/assets`.
- Проверить git status и убедиться, что `static/` не изменялся.

## Acceptance criteria

- [x] `/assets/password-reset-illustration.png` больше не является битым изображением.
- [x] Frontend-копия совпадает с валидным read-only исходником из `static/assets`.
- [x] `static/` не редактировался.

## Postman

Postman collection не обновляется, потому что API endpoints и контракты не менялись.
