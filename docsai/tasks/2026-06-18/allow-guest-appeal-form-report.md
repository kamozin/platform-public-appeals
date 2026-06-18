# Guest appeal form report

## Что сделано

- `/appeal/new` больше не требует авторизацию и не редиректит гостя на `/login`.
- Добавлен guest token workflow для `appeal-drafts`.
- Гостевой draft token хранится на клиенте в cookie `rd_appeal_draft_token`.
- API принимает `X-Appeal-Draft-Token` для чтения, обновления, удаления, загрузки вложений и отправки гостевого черновика.
- В базе для гостевых черновиков хранится только `guest_token_hash`.
- CTA на главной странице теперь ведёт на `/appeal/new`.

## Затронутые файлы

- `app/Application/Appeals/AppealDraftAccess.php`
- `app/Application/Appeals/AppealDraftService.php`
- `app/Application/Auth/AuthService.php`
- `app/Http/Controllers/Api/Appeals/AppealDraftController.php`
- `app/Http/Controllers/Api/Appeals/AppealDraftAttachmentController.php`
- `app/Http/Controllers/Api/Appeals/AppealDraftSubmitController.php`
- `app/Models/AppealDraft.php`
- `database/migrations/2026_06_18_000002_allow_guest_appeal_drafts.php`
- `frontend/app/pages/appeal/new.vue`
- `frontend/app/types/api/private.ts`
- `frontend/app/components/home/HomeHero.vue`
- `tests/Feature/Api/AppealDraftEndpointTest.php`
- `docs/development/private-workflows-api.md`
- `docs/development/frontend-private-workflows.md`
- `docsai/postmancollection/rukadobra-api.postman_collection.json`

## Проверки

- Добавлены feature tests для гостевого создания, обновления, чтения, отправки и вложений.

## Postman

- Коллекция обновлена примером гостевого draft workflow.
