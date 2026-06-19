# Отчёт: backend-модерация обращений

## Что сделано

- Добавлен backend workflow модерации отправленных `appeal_drafts`.
- Добавлены статусы `needs_changes`, `rejected`, `approved` поверх существующего `pending_moderation`.
- Добавлен audit trail действий модератора в `appeal_moderation_events`.
- Добавлены admin endpoints для очереди, detail, запроса правок, отклонения и одобрения.
- `approve` создаёт публичное обращение через существующий `AdminContentService` и связывает draft с `public_appeal_id`.
- Пользователь может повторно отправить draft из `needs_changes`.

## Затронутые файлы

- `database/migrations/2026_06_19_000005_create_appeal_moderation_tables.php`
- `app/Models/AppealDraft.php`
- `app/Models/AppealModerationEvent.php`
- `app/Application/Admin/AppealModerationService.php`
- `app/Application/Appeals/AppealDraftService.php`
- `app/Http/Controllers/Api/Admin/AdminAppealModerationController.php`
- `app/Http/Requests/Api/Admin/AdminAppealModeration*Request.php`
- `routes/api.php`
- `tests/Feature/Api/AppealModerationEndpointTest.php`
- `frontend/app/types/api/private.ts`
- `frontend/app/pages/dashboard.vue`
- `docs/development/private-workflows-api.md`
- `docs/development/public-content-api.md`
- `docsai/postmancollection/rukadobra-api.postman_collection.json`

## Проверки

- `docker compose exec -T laravel php artisan test tests/Feature/Api/AppealModerationEndpointTest.php`
- `docker compose exec -T laravel php artisan test tests/Feature/Api/AppealDraftEndpointTest.php tests/Feature/Api/AdminContentEndpointTest.php tests/Feature/Api/AppealModerationEndpointTest.php`
- `docker compose exec -T laravel vendor/bin/pint --test <затронутые backend-файлы>`
- `docker compose exec -T laravel composer analyse`
- `docker compose exec -T laravel composer test`
- `docker compose exec -T nuxt pnpm typecheck`

`docker compose exec -T laravel composer lint` отдельно не прошёл из-за существующей style-проблемы в `app/Application/Auth/VerificationService.php`, не связанной с задачей. Затронутые backend-файлы прошли Pint по отдельности, PHPStan прошёл без ошибок.

## Postman

Postman collection обновлена: добавлена папка `Admin Appeal Moderation` с запросами list/show/request changes/reject/approve.
