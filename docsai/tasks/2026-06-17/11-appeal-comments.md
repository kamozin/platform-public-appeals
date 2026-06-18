# Appeal comments

## Context

В `static/appeal-detail.html` есть комментарии, фильтры "Все / Официальные / С фото" и форма добавления комментария. Сейчас новый комментарий создается в DOM и получает статус "На проверке".

## Goal

Реализовать список и создание комментариев к обращению.

## Non-goals

- Не реализовывать вложенные ответы.
- Не реализовывать модераторский интерфейс.
- Не реализовывать лайки комментариев.

## Backend changes

- Добавить `GET /api/v1/appeals/{appeal}/comments`.
- Добавить `POST /api/v1/appeals/{appeal}/comments`.
- Валидация через FormRequest.
- Создание через DTO/use-case.
- Новый публичный комментарий получает статус `pending`.
- Официальные комментарии доступны только как read model.

## Frontend changes

- Комментарии на detail page получать через API.
- Фильтры official/media делать через query или локально по уже загруженному списку, в зависимости от объема.
- Форма отправляет комментарий через API.
- После отправки показывать pending state.

## API contract

### `GET /api/v1/appeals/{appeal}/comments`

Route name: `api.appeals.comments.index`.

### `POST /api/v1/appeals/{appeal}/comments`

Route name: `api.appeals.comments.store`.

Request:

```json
{
  "comment": "Текст комментария"
}
```

Success `201`:

```json
{
  "data": {
    "id": "uuid",
    "status": "pending",
    "comment": "Текст комментария"
  }
}
```

Errors:
- `VALIDATION_FAILED` -> 422;
- `UNAUTHORIZED` -> 401;
- `NOT_FOUND` -> 404.

## SEO impact

Комментарии могут быть SSR-rendered как часть публичной страницы, но форма отправки работает client-side.

## Tests

- Application test create comment.
- Feature tests index/store/401/422/404.

## Acceptance criteria

- [ ] Список комментариев отдается API.
- [ ] Авторизованный пользователь может отправить комментарий.
- [ ] Гость получает 401 при отправке.
- [ ] Пустой комментарий дает 422.
- [ ] Postman обновлен.

## Postman

- [ ] Добавить comments index/store.
- [ ] Добавить примеры 401/422.

