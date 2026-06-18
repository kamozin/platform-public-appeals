# 15 Appeal Attachments SmartCaptcha Submit Report

## Что сделано

- Добавлены attachment upload/delete endpoints.
- Добавлен submit endpoint для черновика.
- Upload проверяет тип, размер и лимит 10 файлов.
- Submit принимает dev captcha token `test-captcha` / `dev-captcha` и переводит черновик в `pending_moderation`.
- `/appeal/new` поддерживает прикрепление файла и отправку.

## Проверки

- Feature tests upload/delete/submit.
- Backend lint и Nuxt typecheck/lint.

## Postman

Добавлены attachments и submit endpoints.
