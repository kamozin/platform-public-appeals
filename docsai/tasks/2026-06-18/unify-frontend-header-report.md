# Отчёт: унифицировать frontend header

## Что сделано

- `AppHeader` сделан единственным компонентом шапки для `default` и `auth` layout-ов.
- Удалён неиспользуемый `AuthHeader`, чтобы не было второго варианта правого блока действий.
- В `AppHeader` правые действия приведены к одному порядку и тексту: `Войти`, затем `Подать обращение`.
- Для текстовой ссылки входа добавлен отдельный класс `auth-login-link`, чтобы текст `Войти` не попадал в круглый icon-only стиль `auth-account-link`.
- Мобильное меню обновлено так, чтобы новая ссылка входа растягивалась на всю ширину вместе с CTA.
- Документация frontend layout обновлена на русском.

## Затронутые файлы

- `frontend/app/components/layout/AppHeader.vue`
- `frontend/app/components/layout/AuthHeader.vue` удалён
- `frontend/app/assets/styles/main.css`
- `docs/development/frontend-layout.md`
- `docsai/tasks/2026-06-18/unify-frontend-header.md`

## Проверки

- `docker run --rm -v "\\wsl.localhost\Ubuntu-22.04\home\kamozin\zhaloba\frontend:/app" -w /app node:22-bookworm-slim sh -lc "corepack enable && corepack prepare pnpm@11.7.0 --activate && pnpm typecheck"` — успешно.
- `docker run --rm -v "\\wsl.localhost\Ubuntu-22.04\home\kamozin\zhaloba\frontend:/app" -w /app node:22-bookworm-slim sh -lc "corepack enable && corepack prepare pnpm@11.7.0 --activate && pnpm lint"` — успешно.
- `docker run --rm -v "\\wsl.localhost\Ubuntu-22.04\home\kamozin\zhaloba\frontend:/app" -w /app node:22-bookworm-slim sh -lc "corepack enable && corepack prepare pnpm@11.7.0 --activate && pnpm build"` — успешно, с предупреждением Nuxt/Vite про sourcemap `nuxt:module-preload-polyfill`.
- `curl -k https://rukadobra.localhost/` и `curl -k https://rukadobra.localhost/book` — публичные SSR-страницы содержат `auth-login-link`, `Войти` и `Подать обращение`.

## Примечания

- Проверки запускались в Debian-based Node 22 контейнере, потому что текущий `docker compose` сервис `nuxt` использует Alpine, а установленный `node_modules` содержит glibc binding `@oxc-parser/binding-linux-x64-gnu`. В Alpine проверка падает до запуска Nuxt из-за отсутствующего `@oxc-parser/binding-linux-x64-musl`.
- `/login`, `/register`, `/verification`, `/password-reset`, `/appeal/new` и `/dashboard` в `nuxt.config.ts` имеют `ssr: false`, поэтому через curl их HTML ожидаемо пустой до client-side рендера.
- `static/` не изменялся.

## Postman

Не обновлялся: API endpoints и контракты не менялись.
