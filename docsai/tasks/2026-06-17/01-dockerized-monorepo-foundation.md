# Dockerized monorepo foundation

## Context

Сейчас в репозитории есть только `AGENTS.md`, `docsai/` и исходная верстка в `static/`. Целевая архитектура по `AGENTS.md`: Laravel 13 API-only + Nuxt 4 SSR в monorepo. Все локальное окружение должно запускаться в Docker, работать через единый reverse proxy и поддерживать локальный HTTPS.

## Goal

Поднять базовый Docker dev environment для дальнейшей разработки backend и frontend.

## Non-goals

- Не переносить страницы из `static/`.
- Не реализовывать бизнес API.
- Не настраивать production deploy.
- Не коммитить приватные TLS-сертификаты.

## Backend changes

- Подготовить место для Laravel-приложения в корне monorepo.
- Заложить PHP-FPM service для Laravel.
- Заложить подключение Laravel к MySQL, Redis, Mailpit и Gotenberg через env/config.

## Frontend changes

- Подготовить место для Nuxt-приложения в `frontend/`.
- Заложить Node/Nuxt service для SSR.
- Настроить доступ Nuxt к Laravel API:
  - server-side: внутренний Docker URL;
  - browser-side: same-origin `/api/v1`.

## API contract

Новых endpoint-ов в этой задаче нет.

## SEO impact

Нет SEO-страниц. Важное инфраструктурное требование: все публичные frontend URL должны открываться через HTTPS на локали.

## Tests

- Проверить, что Docker services стартуют.
- Проверить, что HTTPS reverse proxy отвечает.
- Проверить, что Mailpit UI открывается.
- Проверить, что Laravel сможет обращаться к Gotenberg по внутреннему Docker URL после появления Laravel-кода.

## Acceptance criteria

- [x] Есть `docker-compose.yml`.
- [x] Есть `nginx` reverse proxy.
- [x] `http://rukadobra.localhost` редиректит на `https://rukadobra.localhost`.
- [x] `https://rukadobra.localhost/api/*` проксируется в Laravel.
- [x] `https://rukadobra.localhost/*` проксируется в Nuxt SSR.
- [x] `https://mail.rukadobra.localhost` открывает Mailpit UI.
- [x] Есть MySQL service.
- [x] Есть Redis service.
- [x] Есть Mailpit service.
- [x] Есть Gotenberg service.
- [x] TLS-сертификаты генерируются локально через `mkcert`.
- [x] Сертификаты не попадают в git.
- [x] В документации описан запуск окружения и hosts-записи.

## Postman

Не требуется.
