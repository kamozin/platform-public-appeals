# 07 Categories API and Page Report

## Что сделано

- Добавлен `GET /api/v1/categories` с route name `api.categories.index`.
- Добавлен DTO с группами категорий и SEO-метаданными.
- Создана SSR-страница Nuxt `/categories`.
- Ссылки категорий ведут на `/appeal/new?category=<slug>`.
- Обновлены header/footer и preview-блок категорий на главной.

## Затронутые файлы

- `routes/api.php`
- `app/Application/PublicContent/PublicContentService.php`
- `app/Http/Controllers/Api/Categories/CategoryController.php`
- `frontend/app/pages/categories.vue`
- `frontend/app/types/api/public-content.ts`
- `frontend/app/components/home/HomeCategoriesPreview.vue`
- `docs/development/public-content-api.md`
- `docs/development/frontend-public-content-pages.md`
- `docsai/postmancollection/rukadobra-api.postman_collection.json`

## Проверки

- `composer test`
- `composer lint`
- `pnpm typecheck`
- `pnpm lint`

## Acceptance criteria

- [x] API отдаёт категории группами.
- [x] API содержит SEO DTO.
- [x] Страница `/categories` рендерится через SSR.
- [x] Ссылки категорий ведут на маршрут подачи обращения с выбранной категорией.
- [x] Postman collection обновлена.
