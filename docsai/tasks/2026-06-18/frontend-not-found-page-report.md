# 404 страница в стилистике проекта — отчёт

## Что сделано

- Добавлена кастомная Nuxt error page `frontend/app/error.vue`.
- Для статуса 404 показывается фирменный экран «Страница не найдена».
- Для прочих ошибок используется тот же экран с нейтральным сообщением.
- Добавлены переходы:
  - «На главную»;
  - «Смотреть обращения»;
  - «Выбрать категорию»;
  - «Народная книга».
- Для error page выставляется `noindex,nofollow`.
- Добавлены адаптивные CSS-правила в общей стилистике проекта.

## Затронутые файлы

- `docsai/tasks/2026-06-18/frontend-not-found-page.md`
- `docsai/tasks/2026-06-18/frontend-not-found-page-report.md`
- `frontend/app/error.vue`
- `frontend/app/assets/styles/main.css`

## Backend

Backend не менялся.

## API contract

API-контракт не менялся.

## SEO

- Error page отдаёт `robots: noindex,nofollow`.
- Canonical для 404 не добавлялся.
- Sitemap и robots routes не менялись.

## Проверки

- `docker compose exec -T nuxt pnpm lint` — успешно.
- `docker compose exec -T nuxt pnpm typecheck` — успешно.
- SSR smoke через dev server:
  - неизвестный URL отдаёт HTTP `404`;
  - HTML содержит `not-found-page`;
  - HTML содержит `noindex,nofollow`.
- `docker compose exec -T nuxt pnpm build` — не пройден из-за уже существующей проблемы вне этой задачи: `frontend/app/pages/register.vue` ссылается на отсутствующий `/assets/auth-register-illustration.svg`.

## Postman

Postman collection не обновлялась, потому что API endpoints не менялись.
