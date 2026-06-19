# Frontend SSR foundation

## Context

Frontend должен быть Nuxt 4 / Vue 3 / TypeScript / SSR. Laravel не рендерит HTML. Публичные страницы должны индексироваться и получать данные через SSR-safe API client.

## Goal

Создать базовый Nuxt SSR foundation для переноса верстки из `static/`.

## Non-goals

- Не переносить конкретные страницы.
- Не подключать бизнес API, кроме проверки health при необходимости.
- Не менять `static/`.

## Backend changes

Нет.

## Frontend changes

- Инициализировать `frontend/`.
- Включить SSR.
- Включить строгий TypeScript.
- Настроить `runtimeConfig`:
  - private `apiInternalBase`;
  - public `apiBase`;
  - public `siteUrl`.
- Добавить общий `useApi`.
- Добавить базовый default layout.
- Добавить базовые SEO helpers.
- Подготовить структуру `components/`, `composables/`, `types/`, `assets/`, `pages/`.

## API contract

Новых backend endpoint-ов нет.

## SEO impact

- Заложить SSR-first fetching.
- Заложить canonical helper.
- Заложить noindex-подход для приватных страниц.

## Tests

- Проверить `pnpm build` или script из package manager lockfile.
- Проверить TypeScript typecheck.
- Если тестовый стек добавлен: smoke test базовой страницы.

## Acceptance criteria

- [x] Nuxt работает в SSR.
- [x] `useApi` выбирает правильный base URL на сервере и в браузере.
- [x] Нет hardcoded `/api/v1` по компонентам.
- [x] TypeScript strict включен.
- [x] Build проходит.

## Postman

Не требуется.
