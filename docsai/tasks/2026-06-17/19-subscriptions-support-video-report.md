# 19 Subscriptions Support Video Report

## Что сделано

- Добавлены `POST/DELETE /api/v1/subscriptions/news`.
- Добавлен `POST /api/v1/support-videos`.
- Footer subscription и блок видео поддержки подключены к API.
- Видео сохраняется со статусом `pending_moderation`.

## Проверки

- Feature tests `SubscriptionEndpointTest`.
- Backend lint и Nuxt typecheck/lint.

## Postman

Добавлены subscriptions/support-videos endpoints.
