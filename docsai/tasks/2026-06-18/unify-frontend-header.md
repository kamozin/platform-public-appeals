# Унифицировать frontend header

## Context

В Nuxt frontend есть общий `AppHeader`, но рядом остался старый `AuthHeader` с отличающейся разметкой правых действий. Из-за этого легко получить разные варианты шапки на публичных и auth-страницах.

## Goal

Сделать `AppHeader` единственным компонентом шапки и привести правый блок действий к одному виду: вход и подача обращения должны отображаться одинаково на всех layout-ах.

## Non-goals

- Не менять исходники в `static/`.
- Не менять backend API.
- Не менять маршруты страниц.
- Не переносить новые страницы из статической верстки.

## Backend changes

Не требуются.

## Frontend changes

- Оставить единый `frontend/app/components/layout/AppHeader.vue`.
- Удалить неиспользуемый `frontend/app/components/layout/AuthHeader.vue`.
- Убрать визуальный конфликт, при котором текст входа попадал в стили icon-only аккаунт-кнопки.
- Обновить общие стили header только в пределах существующего shell.

## API contract

Не меняется.

## SEO impact

SSR и meta-теги не меняются. Навигационные ссылки остаются обычными `NuxtLink`, доступными в SSR-разметке.

## Tests

- `pnpm typecheck`
- `pnpm lint`
- `pnpm build`

## Acceptance criteria

- [x] Во frontend остался один используемый компонент header.
- [x] Auth layout и default layout используют одинаковую шапку.
- [x] Правые действия header не различаются между публичными и auth-страницами.
- [x] Текст `Войти` не обрезается и не выходит за пределы круглой icon-only кнопки.
- [x] `static/` не изменён.

## Postman

Не требуется: API endpoints не меняются.
