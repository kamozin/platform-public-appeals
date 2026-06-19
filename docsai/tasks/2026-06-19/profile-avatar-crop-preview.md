# Управляемый crop аватарки перед загрузкой

## Context

В личном кабинете уже есть загрузка аватарки через существующие endpoint-ы профиля. Frontend проверяет тип и размер файла, делает автоматический квадратный crop по центру, уменьшает изображение до `512x512`, показывает локальный preview и отправляет результат в API.

Пользователю нужно дать контроль над кадрированием до загрузки: выбрать видимую область, увеличить/уменьшить изображение, повернуть его и увидеть, что именно будет сохранено.

## Goal

Добавить управляемую crop-модалку для аватарки профиля:

- после выбора файла открыть preview перед отправкой;
- показать текущую область будущей аватарки;
- дать drag-перемещение изображения;
- дать zoom через slider;
- дать поворот на 90 градусов;
- после подтверждения создать `512x512` WebP;
- отправить подтверждённый cropped файл в `POST /profile/avatar`;
- показывать обработанный preview во время upload;
- при ошибке возвращать предыдущую аватарку или инициалы.

## Non-goals

- Не менять backend-контракт загрузки аватарки.
- Не менять storage-диск и директорию `avatars/`.
- Не добавлять npm-зависимость cropper-библиотеки.
- Не трогать исходную папку `static/`.
- Не переносить обработку аватарки на backend.

## Backend changes

Backend не меняется. Используются существующие endpoint-ы:

- `POST /api/v1/profile/avatar`;
- `DELETE /api/v1/profile/avatar`.

## Frontend changes

- Обновить `frontend/app/pages/dashboard.vue`.
- Добавить состояние выбранного файла, crop-модалки, zoom, offset и rotation.
- Использовать browser canvas API для финального crop/resize.
- Оставить общий `useApi()` и `useAuth()`.
- Добавить стили crop-модалки в `frontend/app/assets/styles/main.css`.

## API contract

Контракт не меняется:

```txt
POST /api/v1/profile/avatar
Content-Type: multipart/form-data
field: avatar
201 { "data": AuthUserDto }

DELETE /api/v1/profile/avatar
204
```

Ожидаемые ошибки:

- `UNAUTHORIZED`;
- `VALIDATION_FAILED`;
- `AVATAR_TYPE_NOT_ALLOWED`;
- `AVATAR_TOO_LARGE`;
- `AVATAR_UPLOAD_FAILED`.

## SEO impact

Нет влияния. `/dashboard` остаётся приватной noindex-страницей.

## Tests

- `pnpm typecheck` во frontend.
- `pnpm lint` во frontend.
- `php artisan test --filter DashboardProfileEndpointTest`.

## Acceptance criteria

- [x] После выбора файла открывается crop-модалка, а upload не начинается автоматически.
- [x] Пользователь видит круглый preview будущей аватарки.
- [x] Пользователь может переместить изображение внутри crop-зоны.
- [x] Пользователь может изменить zoom slider-ом.
- [x] Пользователь может повернуть изображение на 90 градусов.
- [x] После подтверждения frontend создаёт `512x512` WebP и отправляет его в существующий API.
- [x] Во время upload показывается локальный cropped preview.
- [x] При отмене выбранный файл и временный preview очищаются.
- [x] При ошибке upload возвращается предыдущая аватарка или инициалы.

## Postman

Postman collection не меняется, потому что API-контракт остаётся прежним.
