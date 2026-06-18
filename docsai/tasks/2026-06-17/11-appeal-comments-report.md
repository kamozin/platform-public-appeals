# 11 Appeal Comments Report

## Что сделано

- Добавлен `POST /api/v1/appeals/{appeal}/comments`.
- `GET /api/v1/appeals/{appeal}/comments` теперь объединяет статический read-model и сохранённые комментарии.
- Новый комментарий создаётся со статусом `pending`.
- Страница `/appeals/[slug]` получает комментарии через API, поддерживает фильтры и форму отправки.

## Проверки

- `docker compose exec -T laravel composer test`
- `docker compose exec -T laravel composer lint`
- `pnpm typecheck`
- `pnpm lint`

## Postman

Коллекция обновлена: comments index/store.
