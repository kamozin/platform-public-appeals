# Хранение аватарок профиля

Аватарка профиля загружается через `POST /api/v1/profile/avatar` в поле `avatar` формата `multipart/form-data`.

Backend принимает `image/jpeg`, `image/png`, `image/webp` до 2 МБ и сохраняет файл на Laravel disk `public` в директорию `avatars/`.

Публичный URL возвращается в поле `data.avatarUrl`:

```txt
/storage/avatars/<file>
```

Для локального Docker-окружения нужен symlink Laravel storage:

```bash
docker compose exec -T laravel php artisan storage:link
```

Nginx отдаёт `/storage/*` из `/var/www/html/public/storage`, поэтому загруженный файл доступен по URL, который возвращает API.

Laravel-контейнер собирается с UID/GID из `APP_UID` и `APP_GID` или `1000:1000` по умолчанию. Это нужно, чтобы PHP-FPM мог писать в bind-mounted `storage` в WSL.

При повторной загрузке старая аватарка удаляется после успешного сохранения новой. При `DELETE /api/v1/profile/avatar` поле `avatar_path` очищается, а файл удаляется из public storage.

## Frontend crop перед загрузкой

На странице `/dashboard` пользователь выбирает исходный JPG, PNG или WebP до 10 МБ. Frontend не отправляет файл сразу: сначала открывается модальное окно кадрирования.

В crop-модалке можно:

- переместить изображение внутри круглого preview;
- изменить масштаб slider-ом;
- повернуть изображение на 90 градусов;
- сбросить кадрирование к центру;
- отменить выбор без изменения текущей аватарки.

После подтверждения frontend через browser canvas API создаёт `512x512` WebP-файл и отправляет его в существующий `POST /api/v1/profile/avatar`. Во время upload в профиле показывается локальный cropped preview. Если upload завершается ошибкой, временный preview очищается и снова отображается предыдущая аватарка или инициалы пользователя.
