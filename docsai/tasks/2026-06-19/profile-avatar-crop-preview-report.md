# Отчёт: управляемый crop аватарки перед загрузкой

## Что сделано

- В `/dashboard` выбор файла аватарки теперь открывает crop-модалку, а не запускает upload сразу.
- Добавлен круглый preview будущей аватарки с drag-перемещением изображения.
- Добавлен slider масштаба, поворот на 90 градусов и сброс кадра к центру.
- После подтверждения frontend создаёт через canvas `512x512` WebP-файл.
- Во время upload в профиле отображается локальный cropped preview.
- При отмене очищаются object URL и временное состояние выбранного файла.
- При ошибке upload временный preview очищается, поэтому пользователь снова видит предыдущую аватарку или инициалы.

## Затронутые файлы

- `frontend/app/pages/dashboard.vue`
- `frontend/app/assets/styles/main.css`
- `docs/development/profile-avatar-storage.md`
- `docsai/tasks/2026-06-19/profile-avatar-crop-preview.md`

## Проверки

- `docker compose exec -T nuxt pnpm typecheck` — пройдено.
- `docker compose exec -T nuxt pnpm lint` — пройдено.
- `docker compose exec -T laravel php artisan test --filter DashboardProfileEndpointTest` — пройдено, 2 теста, 32 assertions.

## Postman

Postman collection не обновлялась, потому что API-контракт не изменился. Используются существующие запросы:

- `POST /profile/avatar`;
- `DELETE /profile/avatar`.
