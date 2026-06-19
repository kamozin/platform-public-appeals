# Отчёт: сохранение прочитанности уведомлений

## Что сделано

- Добавлена таблица `user_notification_reads` для хранения прочитанных системных уведомлений по пользователям.
- `GET /api/v1/dashboard/notifications` теперь возвращает `read` на основе сохранённого состояния пользователя.
- `POST /api/v1/dashboard/notifications/mark-all-read` теперь записывает прочтение всех текущих системных уведомлений.
- Обновлён feature test личного кабинета: проверяет, что после отметки прочитанности повторный `GET` возвращает `read: true`.

## Затронутые файлы

- `database/migrations/2026_06_19_000004_create_user_notification_reads_table.php`
- `app/Application/Dashboard/DashboardService.php`
- `app/Http/Controllers/Api/Dashboard/DashboardController.php`
- `tests/Feature/Api/DashboardProfileEndpointTest.php`
- `docsai/tasks/2026-06-19/fix-dashboard-notification-read-state.md`

## Проверки

- `docker compose exec -T laravel php artisan test tests/Feature/Api/DashboardProfileEndpointTest.php` — успешно, 5 тестов, 65 assertions.
- `docker compose exec -T laravel php artisan migrate --force` — успешно.
- `git diff --check` — успешно.

## Postman

Новые endpoints не добавлялись. Коллекция Postman не менялась.
