# Subscriptions and support video

## Context

На главной и в footer есть подписка на новости. На главной есть modal "Прислать видео поддержки" с файлом MP4/MOV до 100 МБ. Сейчас отправка имитируется в DOM.

## Goal

Реализовать подписки на новости и отправку видео поддержки на модерацию.

## Non-goals

- Не делать админку модерации видео.
- Не публиковать видео автоматически.
- Не реализовывать email campaign service.

## Backend changes

- Endpoint-ы:
  - `POST /api/v1/subscriptions/news`;
  - `DELETE /api/v1/subscriptions/news`;
  - `POST /api/v1/support-videos`.
- Валидация email.
- Валидация video file:
  - MP4/MOV;
  - max 100 МБ.
- Сохранять статус `pending_moderation`.

## Frontend changes

- Подключить subscribe forms.
- Подключить support video modal.
- Показать success/error states.

## API contract

Ошибки:
- `VALIDATION_FAILED`;
- `VIDEO_TYPE_NOT_ALLOWED`;
- `VIDEO_TOO_LARGE`;
- `RATE_LIMITED`.

## SEO impact

Нет прямого влияния. Формы не должны ломать SSR.

## Tests

- Feature tests subscribe/unsubscribe.
- Feature test video upload validation.
- Application tests duplicate subscription behavior.

## Acceptance criteria

- [ ] Email subscription создается идемпотентно.
- [ ] Видео отправляется на модерацию.
- [ ] Большой файл возвращает стабильную ошибку.
- [ ] Неверный тип файла возвращает стабильную ошибку.
- [ ] Postman обновлен.

## Postman

- [ ] Добавить subscriptions/support-videos endpoints.

