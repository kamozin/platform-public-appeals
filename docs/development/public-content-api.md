# Публичный контент API

Документ описывает временный публичный read-model для категорий, новостей, обращений и sitemap URL.

## Назначение

API отдаёт JSON DTO для SSR-страниц Nuxt:

- `GET /api/v1/categories`
- `GET /api/v1/news`
- `GET /api/v1/news/{slug}`
- `GET /api/v1/appeals`
- `GET /api/v1/appeals/{slug}`
- `GET /api/v1/appeals/{appeal}/comments`
- `GET /api/v1/seo/sitemap-urls`

Laravel остаётся API-only и не рендерит HTML. Nuxt получает данные через общий `useApi()` и выбирает internal/same-origin base URL через runtime config.

## Текущая реализация

Источник данных находится в `App\Application\PublicContent\PublicContentService`.

Это статический read-model для переноса публичных страниц и стабилизации контрактов. Его можно заменить на Infrastructure query services и репозитории без изменения frontend-контракта.

## Фильтры обращений

`GET /api/v1/appeals` принимает query-параметры:

- `search`
- `status`: `checking`, `active`, `resolved`
- `city`
- `category`
- `sort`: `newest`, `popular`, `resolved`
- `page`
- `per_page`

Валидация выполняется через `AppealIndexRequest`. Отфильтрованные страницы получают `seo.robots = noindex,follow`.

## Ошибки

Отсутствующие новости, обращения и приватные черновики возвращают общий API error contract:

```json
{
  "error": {
    "code": "NOT_FOUND",
    "message": "Resource not found.",
    "details": {},
    "trace_id": "..."
  }
}
```

## Проверки

Контракт покрыт feature-тестами:

- `tests/Feature/Api/CategoriesEndpointTest.php`
- `tests/Feature/Api/NewsEndpointTest.php`
- `tests/Feature/Api/AppealsEndpointTest.php`
- `tests/Feature/Api/SeoEndpointTest.php`
