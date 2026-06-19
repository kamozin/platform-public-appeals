# Исправить битые иллюстрации входа и регистрации

## Context

На страницах `/login` и `/register` Nuxt ссылался на публичные ассеты:

- `/assets/auth-login-illustration.svg`
- `/assets/auth-register-illustration.svg`

Файлы существовали в `frontend/public/assets`, но были повреждены: содержали нулевые байты вместо валидного SVG, из-за чего браузер показывал битую картинку.

## Goal

Заменить повреждённые frontend-копии иллюстраций на валидные SVG, чтобы страницы входа и регистрации не показывали broken image.

## Non-goals

- Не менять backend API.
- Не менять разметку и обработчики форм авторизации.
- Не менять `static/`, потому что исходная статическая вёрстка read-only.
- Не менять nginx/Nuxt routing.

## Backend changes

Backend не менялся.

## Frontend changes

- Заменён `frontend/public/assets/auth-login-illustration.svg` на валидный SVG.
- Заменён `frontend/public/assets/auth-register-illustration.svg` на валидный SVG.

## API contract

API-контракт не менялся.

## SEO impact

SEO не менялось. Страницы `/login` и `/register` остаются auth-экранами с текущими route rules.

## Tests

- XML-парсинг обоих SVG через PowerShell `[xml](Get-Content -Raw ...)`.
- Проверка байтов: файлы не содержат NUL bytes и начинаются с `<svg`.
- Проверка Nuxt-контейнера: оба файла доступны внутри контейнера по `/app/public/assets/...`.
- HTTP-проверка напрямую через Nuxt: оба ассета отдаются с `200 OK` и `Content-Type: image/svg+xml`.
- HTTP-проверка через nginx на `https://rukadobra.localhost`: оба ассета отдаются с `200 OK` и `Content-Type: image/svg+xml`.
- HTTP-проверка страниц `/login` и `/register`: обе страницы отдаются с `200 OK`.

Production build не пройден из-за состояния окружения: в текущем `node_modules` отсутствует optional native binding `@oxc-parser/binding-linux-x64-musl`; WSL Node также устарел (`v12.22.9`) для Nuxt 4.

## Acceptance criteria

- [x] `/assets/auth-login-illustration.svg` не битый и отдаётся как `image/svg+xml`.
- [x] `/assets/auth-register-illustration.svg` не битый и отдаётся как `image/svg+xml`.
- [x] `/login` больше не ссылается на повреждённый SVG.
- [x] `/register` больше не ссылается на повреждённый SVG.
- [x] `static/` не редактировался.

## Postman

Postman collection не обновлялась, потому что API endpoints и контракты не менялись.
