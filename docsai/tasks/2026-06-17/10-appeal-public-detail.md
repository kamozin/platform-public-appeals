# Appeal public detail

## Context

`static/appeal-detail.html` содержит публичную карточку обращения: описание, статистику, материалы, ход рассмотрения, официальный ответ, документы, комментарии и действия.

## Goal

Реализовать API и SSR-страницу `/appeals/[slug]` для публичного обращения.

## Non-goals

- Не реализовывать добавление комментариев.
- Не реализовывать поддержку/лайки.
- Не реализовывать редактирование обращения.

## Backend changes

- Добавить `GET /api/v1/appeals/{slug}`.
- Вернуть DTO карточки обращения.
- Вернуть `NOT_FOUND` для отсутствующего slug.
- Подготовить структуры: status timeline, attachments, official response, documents, summary, seo.

## Frontend changes

- Перенести `appeal-detail.html` в `/appeals/[slug]`.
- SSR-fetch данных.
- Обработать 404.
- Share modal использует текущий canonical URL.

## API contract

### `GET /api/v1/appeals/{slug}`

Route name: `api.appeals.show`.

Success DTO включает:
- `id`;
- `slug`;
- `title`;
- `status`;
- `category`;
- `location`;
- `description`;
- `stats`;
- `attachments`;
- `timeline`;
- `officialResponse`;
- `documents`;
- `commentsPreview`;
- `seo`.

## SEO impact

- Страница индексируемая только для опубликованных обращений.
- Неопубликованные или приватные обращения не должны попадать в sitemap.
- Missing slug должен давать Nuxt 404.

## Tests

- Feature test show.
- Feature test 404.
- Application test visibility rules.
- Frontend SSR SEO smoke.

## Acceptance criteria

- [x] Опубликованное обращение отдается по slug.
- [x] Непубличное обращение не отдается как публичная страница.
- [x] Nuxt отдает 404 для missing slug.
- [x] SEO DTO применен в head.
- [x] Postman обновлен.

## Postman

- [x] Добавить `GET /api/v1/appeals/{slug}`.
- [x] Добавить пример 404.
