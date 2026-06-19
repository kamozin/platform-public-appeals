# Отчёт: обработка аватарки профиля на frontend

## Что сделано

- В `/dashboard` добавлен блок управления аватаркой профиля.
- Frontend проверяет MIME type и размер исходного файла.
- Изображение обрабатывается через canvas: центральный квадратный crop, resize до `512x512`, конвертация в WebP.
- Обработанный файл отправляется в существующий `POST /profile/avatar` как `multipart/form-data`.
- Добавлено удаление аватарки через `DELETE /profile/avatar`.
- После upload/delete синхронизируются `profile` и `auth.user`.
- Добавлены состояния pending/success/error рядом с блоком аватарки.

## Затронутые файлы

- `frontend/app/pages/dashboard.vue`
- `frontend/app/assets/styles/main.css`
- `docs/development/frontend-private-workflows.md`
- `docsai/postmancollection/rukadobra-api.postman_collection.json`
- `docsai/tasks/2026-06-19/profile-avatar-frontend-processing.md`

## Проверки

- `docker compose exec -T nuxt pnpm typecheck` — пройдено.
- `docker compose exec -T nuxt pnpm lint` — пройдено.
- `docker compose exec -T laravel php artisan test --filter DashboardProfileEndpointTest` — пройдено.
- JSON-валидность Postman collection — пройдено.

## Postman

В коллекцию добавлены:

- `PATCH /profile`;
- `POST /profile/avatar`;
- `DELETE /profile/avatar`.
