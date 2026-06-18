# Categories API and page

## Context

`static/categories.html` содержит публичную страницу всех категорий обращений: группы "Дом и город", "Социальная сфера", "Права и безопасность". Эти данные должны быть доступны frontend-у через API, чтобы категории использовались и в wizard подачи обращения.

## Goal

Реализовать backend API категорий и SSR-страницу `/categories`.

## Non-goals

- Не делать админку управления категориями.
- Не реализовывать подачу обращения.
- Не делать многоязычность категорий.

## Backend changes

- Создать модель/миграции категорий и групп категорий или seed-based read model.
- Добавить query/use-case для публичного списка категорий.
- Добавить route `GET /api/v1/categories`.
- Добавить resource/DTO с SEO metadata для страницы.

## Frontend changes

- Перенести `static/categories.html` в `/categories`.
- Получать категории через SSR API.
- Ссылки категорий вести на route подачи обращения с выбранной категорией.

## API contract

### `GET /api/v1/categories`

Route name: `api.categories.index`.

Success `200`:

```json
{
  "data": {
    "groups": [
      {
        "slug": "city",
        "title": "Дом и город",
        "categories": [
          {
            "id": "uuid",
            "slug": "housing",
            "title": "ЖКХ",
            "description": "Управляющие компании, подъезды, отопление, вода, лифты",
            "icon": "building"
          }
        ]
      }
    ],
    "seo": {
      "title": "Все категории обращений",
      "description": "Полный список категорий обращений и жалоб.",
      "canonicalUrl": "https://rukadobra.localhost/categories",
      "robots": "index,follow",
      "ogImageUrl": null,
      "lastModifiedAt": null
    }
  }
}
```

## SEO impact

- `/categories` индексируемая SSR-страница.
- Canonical без query params.

## Tests

- Backend feature test `GET /api/v1/categories`.
- Application test на структуру групп.
- Frontend SSR smoke.

## Acceptance criteria

- [ ] API возвращает группы и категории.
- [ ] Nuxt страница рендерит категории на сервере.
- [ ] Ссылки категорий не ведут на `.html`.
- [ ] 0 новых ошибок static analysis.
- [ ] Postman обновлен.

## Postman

- [ ] Добавить `GET /api/v1/categories`.

