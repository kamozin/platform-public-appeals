# Appeal attachments SmartCaptcha submit

## Context

Wizard подачи обращения содержит шаг файлов, шаг Yandex SmartCaptcha и финальную отправку на модерацию. Файлы имеют разные ограничения: фото до 10 МБ, видео до 100 МБ, документы до 20 МБ, максимум 10 файлов.

## Goal

Добавить файлы, SmartCaptcha verification и финальную отправку обращения на модерацию.

## Non-goals

- Не реализовывать модераторский бекофис.
- Не реализовывать публикацию после модерации.
- Не реализовывать OCR/анализ файлов.

## Backend changes

- Storage для attachments.
- Endpoint-ы:
  - `POST /api/v1/appeal-drafts/{id}/attachments`;
  - `DELETE /api/v1/appeal-drafts/{id}/attachments/{attachment}`;
  - `POST /api/v1/appeal-drafts/{id}/submit`.
- Интерфейс SmartCaptcha verifier.
- Infrastructure adapter для Yandex SmartCaptcha.
- Config для captcha secrets через `config/*`.
- Валидация файлов через FormRequest.

## Frontend changes

- Реализовать upload UI из шага 3.
- Показать список прикрепленных файлов.
- Подключить SmartCaptcha widget.
- Submit переводит draft в moderation/pending status.

## API contract

Коды ошибок:
- `VALIDATION_FAILED`;
- `UNAUTHORIZED`;
- `FORBIDDEN`;
- `NOT_FOUND`;
- `ATTACHMENT_LIMIT_EXCEEDED`;
- `ATTACHMENT_TYPE_NOT_ALLOWED`;
- `CAPTCHA_FAILED`;
- `CONFLICT`.

## SEO impact

Нет, wizard noindex.

## Tests

- Application tests submit branches.
- Feature tests upload/delete/submit.
- Fake captcha verifier в тестах.

## Acceptance criteria

- [x] Upload валидирует тип и размер файла.
- [x] Нельзя загрузить больше 10 файлов.
- [x] Submit требует валидную captcha.
- [x] После submit черновик нельзя редактировать как draft.
- [x] Postman обновлен.

## Postman

- [x] Добавить attachments и submit endpoints.
