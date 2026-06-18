# 09 Appeals Public Feed Report

## Что сделано

- Добавлен `GET /api/v1/appeals` с route name `api.appeals.index`.
- Реализованы фильтры `search`, `status`, `city`, `category`, `sort`, `page`, `per_page`.
- Query-параметры валидируются через `AppealIndexRequest`.
- Добавлена SSR-страница `/appeals` с фильтром, сводкой, списком и пагинацией.
- Отфильтрованные страницы получают `seo.robots = noindex,follow`.

## Затронутые файлы

- `routes/api.php`
- `app/Application/PublicContent/PublicContentService.php`
- `app/Http/Controllers/Api/Appeals/AppealController.php`
- `app/Http/Requests/Api/Appeals/AppealIndexRequest.php`
- `frontend/app/pages/appeals/index.vue`
- `frontend/app/types/api/public-content.ts`
- `frontend/app/components/home/HomeAppealsPreview.vue`
- `tests/Feature/Api/AppealsEndpointTest.php`
- `docsai/postmancollection/rukadobra-api.postman_collection.json`

## Проверки

- `composer test`
- `composer lint`
- `pnpm typecheck`
- `pnpm lint`

## Acceptance criteria

- [x] Публичный список не отдаёт приватные черновики.
- [x] Фильтры работают через query string.
- [x] Пагинация есть в API и UI.
- [x] SSR-страница `/appeals` не зависит от `onMounted`.
- [x] Postman collection обновлена.
