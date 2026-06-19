# Исправление общей frontend-верстки и ассетов

## Context

В Nuxt-фронтенде часть страниц использует общий header/footer, а auth-страницы используют отдельные компоненты `AuthHeader` и `AuthFooter`. Из-за этого визуальная оболочка отличается между разделами. Также публичные страницы получают изображения из API без fallback-обработки, поэтому пустой или битый URL может ломать карточки.

## Goal

Привести header и footer к единому виду на всех страницах, улучшить адаптивность auth/private-экранов, добавить fallback для изображений и убедиться, что используемые публичные ассеты доступны из `frontend/public`.

## Non-goals

Не менять исходную папку `static/`.
Не менять backend API и контракты.
Не переделывать дизайн-систему с нуля.
Не добавлять Laravel Blade/UI.

## Backend changes

Не требуются.

## Frontend changes

- Использовать общий `LayoutAppHeader` и `LayoutAppFooter` для auth layout.
- Привести экран подтверждения контакта к общей auth/screen-оболочке.
- Улучшить страницу личного кабинета через уже существующие `cabinet-*` стили.
- Добавить общий fallback для изображений, приходящих из API.
- Скопировать недостающие frontend-ассеты из `static/assets` в `frontend/public/assets`.

## API contract

Без изменений.

## SEO impact

Публичные SSR-страницы сохраняют существующие meta/robots. Приватные страницы остаются `noindex`.

## Tests

- `nuxt prepare`
- `vue-tsc --noEmit`
- `eslint`
- `nuxt build`
- локальная проверка dev server основных страниц

## Acceptance criteria

- Header и footer одинаковые на публичных, auth и private страницах.
- Изображения API-карточек не показывают битую иконку браузера при пустом/ошибочном URL.
- Auth, verification и dashboard не выпадают из общего визуального контекста.
- `static/` не изменён.
- Frontend проверки проходят либо причина блокера явно описана.

## Postman

Не требуется: API endpoints не меняются.
