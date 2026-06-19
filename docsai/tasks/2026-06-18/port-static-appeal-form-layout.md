# Перенести вёрстку формы обращения из static

## Context

Страница `/appeal/new` уже работает с API черновиков и поддерживает гостевую подачу, но её UI упрощён и не соответствует исходной вёрстке из `static/appeal.html`.

Исходная форма в `static/appeal.html` содержит 7-шаговый мастер, progress bar, выбор способа подачи, сетку категорий, блок загрузки файлов, карту, контакты, SmartCaptcha-заглушку, финальную проверку и result screen.

## Goal

Перенести visual structure и классы формы из `static/appeal.html` в Nuxt-страницу `/appeal/new`, сохранив текущую API-логику сохранения черновика, загрузки вложений и отправки.

## Non-goals

- Не редактировать `static/`.
- Не подключать production SmartCaptcha.
- Не реализовывать настоящую карту/геокодинг.
- Не менять backend-контракт, кроме уже существующего guest draft workflow.

## Backend changes

Backend не меняется.

## Frontend changes

- Заменить упрощённый 4-шаговый шаблон `/appeal/new` на 7-шаговый мастер в стиле `static/appeal.html`.
- Перенести нужные CSS-классы из `static/styles.css` во frontend stylesheet.
- Сохранить guest/authed draft headers.
- Подключить реальные поля формы к Vue state.
- Показывать прикреплённые через API файлы в стиле static file list.
- После submit показывать result screen в стиле static.

## API contract

Используются существующие endpoints:

- `POST /api/v1/appeal-drafts`
- `PATCH /api/v1/appeal-drafts/{id}`
- `POST /api/v1/appeal-drafts/{id}/attachments`
- `POST /api/v1/appeal-drafts/{id}/submit`

Для гостя используется `X-Appeal-Draft-Token`.

## SEO impact

`/appeal/new` остаётся `noindex`.

## Tests

- Nuxt typecheck.
- Nuxt lint.
- Backend targeted tests для draft workflow, чтобы не сломать guest/authed API-сценарии.

## Acceptance criteria

- [x] `/appeal/new` визуально соответствует static-мастеру: 7 шагов, progress, side card, review/result screen.
- [x] Гость может заполнить и отправить форму.
- [x] Авторизованный пользователь может заполнить и отправить форму.
- [x] `static/` не изменён.
- [x] Typecheck/lint проходят.

## Postman

Postman не требует изменений: API-контракт не меняется.
