# Fix Profile Avatar Storage Report

## Что сделано

- Аватарки профиля сохраняются на Laravel disk `public`.
- Добавлена проверка неуспешной записи файла, чтобы в `avatar_path` не попадало значение `false` / `0`.
- Старый файл аватарки удаляется при успешной замене.
- Файл аватарки удаляется при `DELETE /api/v1/profile/avatar`.
- Payload профиля больше не возвращает `/storage/0`, если такое повреждённое значение уже есть в БД.
- Laravel Docker image выравнивает UID/GID `www-data` с владельцем bind mount через `APP_UID` / `APP_GID`.
- Nginx отдаёт `/storage/*` из Laravel `public/storage`.

## Затронутые файлы

- `app/Application/Profile/ProfileService.php`
- `app/Application/Auth/AuthService.php`
- `lang/en/api_errors.php`
- `lang/ru/api_errors.php`
- `tests/Feature/Api/DashboardProfileEndpointTest.php`
- `docker-compose.yml`
- `docker/nginx/default.conf`
- `docker/php/Dockerfile`
- `docs/development/profile-avatar-storage.md`
- `docsai/tasks/2026-06-19/fix-profile-avatar-storage.md`

## Проверки

- `docker compose exec -T laravel php -l app/Application/Profile/ProfileService.php`
- `docker compose exec -T laravel php -l app/Application/Auth/AuthService.php`
- `docker compose exec -T laravel php -l lang/ru/api_errors.php`
- `docker compose config --quiet`
- `docker compose exec -T nginx nginx -t`
- `docker compose exec -T laravel vendor/bin/pint --test app/Application/Profile/ProfileService.php app/Application/Auth/AuthService.php lang/en/api_errors.php lang/ru/api_errors.php tests/Feature/Api/DashboardProfileEndpointTest.php`
- `docker compose exec -T laravel php artisan test --filter DashboardProfileEndpointTest`
- Runtime HTTP: `POST /api/v1/profile/avatar` вернул `201`, `GET /storage/avatars/...` вернул `200`, `DELETE /api/v1/profile/avatar` вернул `204`.

## Postman

Коллекция уже содержит `Profile Avatar Upload` и `Profile Avatar Delete`. API path, method и payload не менялись, поэтому обновление коллекции не требуется.
