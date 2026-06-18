# Frontend static assets and layout

## Context

`static/` содержит исходные HTML/CSS/assets и не должен редактироваться. Перед переносом страниц нужен общий Nuxt layout, общие стили, ассеты, header/footer и UI-поведение, которое повторяется почти на всех страницах.

## Goal

Перенести общие визуальные основания из `static/` в Nuxt без подключения бизнес API.

## Non-goals

- Не переносить содержимое конкретных страниц.
- Не реализовывать auth.
- Не менять `static/`.

## Backend changes

Нет.

## Frontend changes

- Скопировать нужные assets в `frontend/public/` или `frontend/assets/`.
- Перенести общий CSS в frontend style entry.
- Создать `AppHeader`, `AppFooter`, `AppLogo`.
- Создать общий modal/share UI при необходимости.
- Перенести поведение:
  - mobile menu;
  - password visibility toggle как компонент/композабл;
  - scroll-to-top;
  - share modal;
  - basic pagination component.

## API contract

Нет.

## SEO impact

- Header/footer должны использовать настоящие ссылки `<NuxtLink>`.
- Декоративные изображения получают `alt=""`.
- Значимые изображения получают осмысленный `alt`.

## Tests

- Frontend build.
- Typecheck.
- Smoke-проверка layout на desktop/mobile viewport, если Playwright уже добавлен.

## Acceptance criteria

- [ ] `static/` не изменен.
- [ ] Header/footer вынесены в компоненты.
- [ ] Общие классы и визуальное поведение сохранены.
- [ ] Навигация использует Nuxt routes, а не `.html`.
- [ ] Нет browser-only API без `onMounted`/client guard.

## Postman

Не требуется.

