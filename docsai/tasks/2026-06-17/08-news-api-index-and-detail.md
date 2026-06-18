# News API index and detail

## Context

`static/news.html` и `static/news-detail.html` описывают публичные новости. Страницы должны быть SSR/SEO и отдавать реальные 404 для несуществующих slug.

## Goal

Реализовать новости: список и детальная страница.

## Non-goals

- Не делать админку новостей.
- Не делать поиск по новостям.
- Не делать комментарии к новостям.

## Backend changes

- Создать storage/read model для новостей.
- Добавить `GET /api/v1/news`.
- Добавить `GET /api/v1/news/{slug}`.
- Вернуть SEO DTO для index/detail.
- Настроить `NOT_FOUND` для отсутствующей новости.

## Frontend changes

- Перенести `news.html` в `/news`.
- Перенести `news-detail.html` в `/news/[slug]`.
- Добавить SSR data fetching.
- Добавить frontend 404 через Nuxt error handling.

## API contract

### `GET /api/v1/news`

Route name: `api.news.index`.

Query:
- `page`;
- `per_page`.

### `GET /api/v1/news/{slug}`

Route name: `api.news.show`.

Success DTO:

```json
{
  "data": {
    "id": "uuid",
    "slug": "legal-support-consultations",
    "title": "Открыта запись на консультации по обращениям в суд",
    "excerpt": "...",
    "content": "...",
    "publishedAt": "2026-06-17T00:00:00+03:00",
    "seo": {
      "title": "...",
      "description": "...",
      "canonicalUrl": "https://rukadobra.localhost/news/legal-support-consultations",
      "robots": "index,follow",
      "ogImageUrl": null,
      "lastModifiedAt": "2026-06-17T00:00:00+03:00"
    }
  }
}
```

## SEO impact

- `/news` и `/news/[slug]` индексируемые.
- Для missing slug Nuxt должен вернуть 404, не 200.

## Tests

- Backend feature tests на index/show/404.
- Frontend SSR smoke на title/meta/canonical.

## Acceptance criteria

- [ ] News index работает с пагинацией.
- [ ] News detail работает по slug.
- [ ] Missing slug возвращает API `NOT_FOUND` и Nuxt 404.
- [ ] Postman обновлен.

## Postman

- [ ] Добавить `GET /api/v1/news`.
- [ ] Добавить `GET /api/v1/news/{slug}`.
- [ ] Добавить пример 404.

