# 08 News API Index and Detail Report

## Что сделано

- Добавлены `GET /api/v1/news` и `GET /api/v1/news/{slug}`.
- Добавлены route names `api.news.index` и `api.news.show`.
- Для списка новостей добавлена валидация `page` и `per_page` через `NewsIndexRequest`.
- Созданы SSR-страницы `/news` и `/news/[slug]`.
- Главная страница использует реальные slug новостей.

## Затронутые файлы

- `routes/api.php`
- `app/Application/PublicContent/PublicContentService.php`
- `app/Http/Controllers/Api/News/NewsController.php`
- `app/Http/Requests/Api/News/NewsIndexRequest.php`
- `frontend/app/pages/news/index.vue`
- `frontend/app/pages/news/[slug].vue`
- `frontend/app/components/home/HomeLatestNews.vue`
- `tests/Feature/Api/NewsEndpointTest.php`
- `docsai/postmancollection/rukadobra-api.postman_collection.json`

## Проверки

- `composer test`
- `composer lint`
- `pnpm typecheck`
- `pnpm lint`

## Acceptance criteria

- [x] Список новостей отдаётся с пагинацией и SEO.
- [x] Детальная новость отдаётся по slug.
- [x] Несуществующий slug возвращает `NOT_FOUND`.
- [x] Nuxt-страницы получают данные на SSR.
- [x] Postman collection обновлена.
