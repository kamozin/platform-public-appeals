# Привести страницы авторизации к исходной вёрстке

## Context

Страницы `/login`, `/register` и `/password-reset` во frontend реализованы как упрощённые формы на базе `auth-panel` и не соответствуют исходным макетам из `static/login.html`, `static/register.html`, `static/password-reset.html`.

В исходной вёрстке используются отдельные auth/screen контейнеры, иллюстрации, блоки преимуществ, иконки и адаптивные CSS-правила, часть которых не перенесена в Nuxt.

## Goal

Привести Nuxt-страницы входа, регистрации и восстановления пароля к структуре и визуальному поведению исходной статической вёрстки, сохранив текущую интеграцию с API и обработчики отправки форм.

## Non-goals

- Не менять backend API.
- Не менять бизнес-логику авторизации, регистрации и восстановления пароля.
- Не редактировать папку `static/`.
- Не добавлять новые публичные SEO-страницы.
- Не менять dashboard, verification и создание обращения.

## Backend changes

Backend не меняется.

## Frontend changes

- Обновить разметку `frontend/app/pages/login.vue` по структуре `static/login.html`.
- Обновить разметку `frontend/app/pages/register.vue` по структуре `static/register.html`.
- Обновить разметку `frontend/app/pages/password-reset.vue` по структуре `static/password-reset.html`.
- Перенести недостающие CSS-правила из `static/styles.css` в `frontend/app/assets/styles/main.css`.
- Скопировать недостающие иллюстрации из `static/assets/` в `frontend/public/assets/`.
- Добавить недостающие SVG-иконки в общий Nuxt sprite/icon type.

## API contract

API-контракт не меняется.

Используются существующие endpoints:

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/password-reset/send`
- `POST /api/v1/auth/password-reset/complete`

## SEO impact

Страницы остаются приватными auth-экранами:

- `noindex` сохраняется через `useNoindexSeo()`;
- route rules `ssr: false` не меняются;
- sitemap/robots менять не требуется.

## Tests

- Запустить frontend lint/typecheck, если соответствующие scripts есть в `frontend/package.json`.
- Проверить сборку или Nuxt prepare, если доступно.
- При возможности визуально проверить страницы в dev server.

## Acceptance criteria

- `/login` использует `auth-card auth-card-login`, правую иллюстрацию и блоки преимуществ из исходного макета.
- `/register` использует `auth-card auth-card-register`, правую иллюстрацию, consent-блоки и блоки преимуществ из исходного макета.
- `/password-reset` использует `screen-card flow-card`, flow-step структуру, правую иллюстрацию и flow-benefits из исходного макета.
- Текущие API submit handlers сохранены.
- Не редактируется `static/`.
- Недостающие assets доступны из `frontend/public/assets`.

## Postman

Обновление Postman collection не требуется, потому что API-контракт и endpoints не меняются.
