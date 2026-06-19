# Enhance News Detail Page Report

## Что сделано

- Детальная страница `/news/[slug]` переведена с минимальной content-композиции на полноценный новостной layout.
- Добавлены категория, дата, время чтения, CTA, крупная обложка с подписью, блок «Что важно», боковая сводка и навигационные ссылки.
- Сохранён SSR-first подход: данные продолжают приходить из `GET /api/v1/news/{slug}`, основной текст рендерится на сервере.
- Форматирование русских дат зафиксировано в таймзоне `Europe/Moscow`, чтобы SSR не сдвигал дату публикации на предыдущий день.
- Для Nuxt-контейнера пересоздан `frontend/node_modules`, потому что старое дерево зависимостей содержало GNU optional bindings, а контейнер использует Alpine/musl.

## Затронутые файлы

- `frontend/app/pages/news/[slug].vue`
- `frontend/app/assets/styles/main.css`
- `frontend/app/utils/formatters.ts`
- `docsai/tasks/2026-06-18/enhance-news-detail-page.md`

## Проверки

- `docker compose exec -T nuxt sh -lc "cd /app && pnpm typecheck"`
- `docker compose exec -T nuxt sh -lc "cd /app && pnpm lint"`
- `curl -k --resolve rukadobra.localhost:443:127.0.0.1 -I https://rukadobra.localhost/news/legal-support-consultations`
- SSR smoke с cache-busting query подтвердил наличие `Что важно`, `Коротко`, `1 минута чтения` и даты `17 июня 2026 г.`

## Postman

Postman collection не менялась, потому что API-контракт не менялся.
