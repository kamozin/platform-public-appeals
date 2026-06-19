# Отчёт: правка карточек обращений в личном кабинете

## Что сделано

- В карточках «Черновики», «Мои обращения» и комментариев добавлены русские подписи для технических статусов и категорий.
- Добавлен переход к редактированию конкретного черновика или обращения на модерации через `draft=<id>`.
- Форма подачи обращения умеет открывать существующий draft, заполнять поля и сохранять изменения без повторной отправки, если обращение уже на модерации.
- Backend разрешает обновлять содержимое и вложения для статуса `pending_moderation`, но повторная отправка и удаление остаются только для `draft`.
- Компактные карточки обращений в личном кабинете получили отдельную сетку без широкой колонки под изображение.

## Затронутые файлы

- `app/Application/Appeals/AppealDraftService.php`
- `frontend/app/pages/dashboard.vue`
- `frontend/app/pages/appeal/new.vue`
- `frontend/app/assets/styles/main.css`
- `tests/Feature/Api/AppealDraftEndpointTest.php`
- `docsai/tasks/2026-06-19/fix-dashboard-appeals-localization-edit-layout.md`

## Проверки

- `docker compose exec -T nuxt sh -lc "pnpm typecheck"` — успешно.
- `docker compose exec -T nuxt sh -lc "pnpm lint"` — успешно.
- `docker compose exec -T laravel php artisan test tests/Feature/Api/AppealDraftEndpointTest.php` — успешно, 6 тестов, 40 assertions.
- `git diff --check` — успешно.

## Postman

Новые endpoints не добавлялись. Коллекция Postman не менялась, потому что используется существующий `PATCH /api/v1/appeal-drafts/{id}`, но расширен допустимый статус редактирования.
