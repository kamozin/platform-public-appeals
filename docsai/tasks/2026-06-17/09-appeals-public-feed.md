# Appeals public feed

## Context

`static/feed.html` содержит публичный реестр обращений с поиском, фильтрами по статусу/городу/категории, сортировкой и пагинацией. Сейчас фильтрация сделана в DOM, но в продукте она должна быть серверной.

## Goal

Реализовать публичный API и SSR-страницу `/appeals`.

## Non-goals

- Не реализовывать детальную страницу обращения.
- Не реализовывать создание обращения.
- Не реализовывать поддержку/лайки.

## Backend changes

- Создать read model обращений.
- Добавить `GET /api/v1/appeals`.
- Поддержать query filters:
  - `search`;
  - `status`;
  - `city`;
  - `category`;
  - `sort`;
  - `page`;
  - `per_page`.
- Вернуть summary counters для страницы.

## Frontend changes

- Перенести `feed.html` в `/appeals`.
- Фильтры синхронизировать с query params.
- Получать данные через SSR/API.
- Пагинацию делать по API.

## API contract

### `GET /api/v1/appeals`

Route name: `api.appeals.index`.

Success `200`:

```json
{
  "data": {
    "items": [
      {
        "id": "uuid",
        "slug": "road-pits-after-repair",
        "title": "Ямы на дороге после ремонта",
        "status": "active",
        "statusLabel": "В работе",
        "city": "Москва",
        "district": "ЮВАО",
        "category": "Дороги",
        "publishedAt": "2026-06-14T10:15:00+03:00",
        "supportCount": 118,
        "viewsCount": 2134,
        "commentsCount": 23,
        "imageUrl": "/assets/issue-road.png"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "perPage": 6,
      "total": 12,
      "lastPage": 2
    },
    "summary": {
      "publishedCount": 1284,
      "resolvedCount": 327,
      "activeCount": 89,
      "supportCount": 48216
    },
    "seo": {
      "title": "Все обращения и жалобы",
      "description": "...",
      "canonicalUrl": "https://rukadobra.localhost/appeals",
      "robots": "index,follow",
      "ogImageUrl": null,
      "lastModifiedAt": null
    }
  }
}
```

## SEO impact

- `/appeals` индексируемая.
- Фильтры по query params по умолчанию `noindex,follow`, если нет отдельной SEO-стратегии.
- Canonical для фильтров указывает на базовую страницу или выбранную стратегию.

## Tests

- Application tests на фильтры и сортировки.
- Feature test index.
- Frontend SSR smoke.

## Acceptance criteria

- [ ] Фильтрация работает серверно.
- [ ] Пагинация работает серверно.
- [ ] Query params не ломают SSR.
- [ ] Empty state отображается.
- [ ] Postman обновлен.

## Postman

- [ ] Добавить `GET /api/v1/appeals` с примерами query params.

