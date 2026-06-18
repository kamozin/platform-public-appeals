# 10 Appeal Public Detail Report

## Что сделано

- Добавлен `GET /api/v1/appeals/{slug}` с route name `api.appeals.show`.
- Детальный DTO содержит описание, статус, вложения, таймлайн, официальный ответ, документы, комментарии-превью и SEO.
- Приватные обращения и отсутствующие slug возвращают `NOT_FOUND`.
- Создана SSR-страница `/appeals/[slug]`.
- Главная страница использует реальные slug публичных обращений.

## Затронутые файлы

- `routes/api.php`
- `app/Application/PublicContent/PublicContentService.php`
- `app/Http/Controllers/Api/Appeals/AppealController.php`
- `app/Http/Controllers/Api/Appeals/AppealCommentController.php`
- `frontend/app/pages/appeals/[slug].vue`
- `frontend/app/types/api/public-content.ts`
- `tests/Feature/Api/AppealsEndpointTest.php`
- `docsai/postmancollection/rukadobra-api.postman_collection.json`

## Проверки

- `composer test`
- `composer lint`
- `pnpm typecheck`
- `pnpm lint`

## Acceptance criteria

- [x] Публичное обращение открывается по slug.
- [x] Приватное обращение не отдаётся публично.
- [x] Страница содержит timeline, материалы и comments preview.
- [x] SEO берётся из API DTO.
- [x] Postman collection обновлена.
