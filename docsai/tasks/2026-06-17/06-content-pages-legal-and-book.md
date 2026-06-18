# Content pages for legal and complaint book

## Context

В верстке есть ссылки на страницы, которых нет в `static/`: `privacy.html`, `agreement.html`, `book.html`. В Nuxt нельзя оставлять битые маршруты.

## Goal

Добавить минимальные страницы:
- `/privacy`;
- `/agreement`;
- `/book`.

## Non-goals

- Не делать CMS.
- Не реализовывать юридически финальные тексты без отдельного согласования.
- Не делать backend API для статических страниц.

## Backend changes

Нет.

## Frontend changes

- Создать Nuxt pages для privacy, agreement, book.
- Добавить базовый текст-заглушку, явно помеченный как требующий финального юридического текста.
- Настроить SEO/noindex по решению:
  - privacy/agreement можно индексировать;
  - book индексировать, если это публичная страница проекта.

## API contract

Нет.

## SEO impact

- У страниц должны быть title, description, canonical.
- Не отдавать 404 для ссылок из layout.

## Tests

- Frontend build.
- Smoke: routes открываются.

## Acceptance criteria

- [ ] `/privacy` существует.
- [ ] `/agreement` существует.
- [ ] `/book` существует.
- [ ] Все ссылки из layout ведут на существующие Nuxt routes.
- [ ] `static/` не изменен.

## Postman

Не требуется.

