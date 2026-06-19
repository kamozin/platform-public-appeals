# Fix Profile Avatar Storage

## Context

`POST /api/v1/profile/avatar` возвращал `500` при загрузке аватарки в локальном Docker-окружении. Диагностика показала две причины: web-процесс PHP-FPM не мог писать в bind-mounted `storage`, а код сохранял аватарку в default `local` disk, хотя `avatarUrl` строится как публичный `/storage/...` URL.

## Goal

Сделать загрузку аватарки стабильной: сохранять файл в публичный disk, не сохранять ложный путь при ошибке записи, удалять старый файл при замене или удалении аватара, а dev-контейнер Laravel должен иметь права на запись в `storage`.

## Non-goals

- Не менять публичный API path.
- Не менять frontend UI загрузки аватарки.
- Не переносить всю файловую архитектуру проекта.

## Backend changes

- В `ProfileService` использовать `public` disk для avatar upload.
- Проверять результат записи файла и возвращать контролируемую API-ошибку при сбое.
- Удалять старый avatar file после успешной замены.
- Удалять avatar file после успешного `DELETE /profile/avatar`.
- Отдавать `/storage/*` через nginx из Laravel `public/storage`.

## Frontend changes

Изменения frontend не требуются.

## API contract

Endpoints остаются прежними:

```txt
POST /api/v1/profile/avatar
DELETE /api/v1/profile/avatar
```

Ответ `avatarUrl` остаётся публичным URL вида `/storage/avatars/...`.

## SEO impact

Нет влияния на SEO: endpoint относится к приватному профилю.

## Tests

- Обновить feature test профиля: использовать `Storage::fake('public')`.
- Проверить, что файл создан на public disk.
- Проверить, что старый файл удаляется при замене.
- Проверить, что файл удаляется при `DELETE /profile/avatar`.

## Acceptance criteria

- [x] `POST /api/v1/profile/avatar` возвращает `201`.
- [x] `avatarUrl` не равен `/storage/0`.
- [x] Файл доступен из public storage path.
- [x] Старый файл удаляется при замене аватарки.
- [x] `DELETE /api/v1/profile/avatar` очищает `avatar_path` и удаляет файл.
- [x] Тест `DashboardProfileEndpointTest` проходит.

## Postman

Коллекция уже содержит `Profile Avatar Upload` и `Profile Avatar Delete`; новые endpoint-ы не добавляются.
