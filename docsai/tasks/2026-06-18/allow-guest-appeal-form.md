# Разрешить гостевое заполнение формы обращения

## Context

Форма подачи обращения уже перенесена в Nuxt на `/appeal/new`, но сейчас страница требует авторизацию через `useAuth().fetchMe()` и backend endpoints `appeal-drafts` привязывают каждый черновик к `user_id`.

Исходная статическая форма `static/appeal.html` предполагала сценарии анонимной подачи и подачи без предварительного входа.

## Goal

Неавторизованный пользователь должен иметь возможность открыть `/appeal/new`, заполнить форму, сохранить черновик, прикрепить файл и отправить обращение на модерацию.

Гостевой черновик должен оставаться приватным: доступ к нему возможен только по выданному draft token.

## Non-goals

- Не переносить весь 7-шаговый UI из `static/appeal.html` в этой задаче.
- Не подключать production SmartCaptcha.
- Не создавать личный кабинет автоматически после гостевой подачи.
- Не менять публичный реестр обращений.

## Backend changes

- Сделать `appeal_drafts.user_id` nullable.
- Сделать `appeal_attachments.user_id` nullable.
- Добавить `appeal_drafts.guest_token_hash` для гостевого доступа.
- При создании черновика без Bearer token генерировать guest token, хранить только hash и вернуть plain token один раз в API response.
- Для чтения, обновления, удаления, upload и submit разрешить доступ владельцу по Bearer token или гостю по `X-Appeal-Draft-Token`.

## Frontend changes

- Убрать обязательный redirect `/appeal/new -> /login`.
- Хранить guest draft token на клиенте.
- Передавать `X-Appeal-Draft-Token` для операций с гостевым черновиком.
- Исправить CTA на главной с `/appeals/create` на `/appeal/new`.

## API contract

Изменяются endpoints:

- `POST /api/v1/appeal-drafts`
- `GET /api/v1/appeal-drafts/{id}`
- `PATCH /api/v1/appeal-drafts/{id}`
- `DELETE /api/v1/appeal-drafts/{id}`
- `POST /api/v1/appeal-drafts/{id}/attachments`
- `DELETE /api/v1/appeal-drafts/{id}/attachments/{attachment}`
- `POST /api/v1/appeal-drafts/{id}/submit`

Для guest-доступа используется header:

```txt
X-Appeal-Draft-Token: <plain-token>
```

`POST /api/v1/appeal-drafts` для гостя возвращает:

```json
{
  "data": {
    "id": "...",
    "guestToken": "..."
  }
}
```

Для авторизованного пользователя `guestToken` равен `null`.

## SEO impact

`/appeal/new` остаётся приватным workflow и должен оставаться `noindex`.

## Tests

- Feature test: guest creates/updates/reads/submits own draft with `X-Appeal-Draft-Token`.
- Feature test: guest draft is not exposed without token.
- Feature test: guest can upload and delete attachment.
- Сохранить существующие tests для авторизованного владельца.

## Acceptance criteria

- [ ] `/appeal/new` открывается без авторизации.
- [ ] Гость может сохранить черновик.
- [ ] Гость может прикрепить файл.
- [ ] Гость может отправить обращение на модерацию.
- [ ] Чужой guest draft не открывается без token.
- [ ] Авторизованный сценарий не сломан.
- [ ] CTA на главной ведёт на `/appeal/new`.

## Postman

Обновить коллекцию: добавить пример гостевого создания черновика и использование `X-Appeal-Draft-Token`.
