# Frontend public home page

## Context

`static/index.html` содержит главную страницу: hero slider, видео поддержки, народная жалобная книга, география, реклама, показатели, категории, как работает, обращения, новости, прозрачность, контакты.

## Goal

Перенести главную страницу в Nuxt SSR как `/`.

## Non-goals

- Не делать динамический backend для всех блоков.
- Не реализовывать отправку видео поддержки.
- Не реализовывать публичную ленту обращений как API в этой задаче.

## Backend changes

Нет или только использование статических mock data внутри frontend.

## Frontend changes

- Создать `frontend/pages/index.vue`.
- Разбить страницу на компоненты:
  - home hero;
  - support video preview;
  - complaint book panel;
  - geography panel;
  - stats strip;
  - categories preview;
  - how it works;
  - appeals preview;
  - latest news preview;
  - transparency/trust blocks.
- Перенести hero slider SSR-safe.
- Настроить SEO meta.

## API contract

Нет новых endpoint-ов.

## SEO impact

- Страница `/` индексируемая.
- Один основной `h1`.
- `title`, `description`, canonical, OG tags.
- Основной контент присутствует в SSR HTML.

## Tests

- Frontend build.
- Typecheck.
- SEO smoke: SSR HTML содержит `h1`, title, description, canonical.

## Acceptance criteria

- [x] Главная визуально соответствует `static/index.html`.
- [x] Контент рендерится SSR.
- [x] Нет client-only основного контента.
- [x] Внутренние ссылки ведут на Nuxt routes.
- [x] `static/` не изменен.

## Postman

Не требуется.
