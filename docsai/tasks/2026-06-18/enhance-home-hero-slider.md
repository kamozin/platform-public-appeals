# Улучшение главного hero-слайдера

## Context

На главной странице hero-слайдер уже перенесен в Nuxt-компонент `HomeHero.vue` и использует существующие изображения, тексты, иконки и CTA-кнопки. Текущая реализация меняет фон через одну CSS-переменную на контейнере, а контент расположен в фиксированной высоте блока. Из-за этого переход выглядит плоско, а при разной длине текстов есть риск визуального сдвига и обрезания нижних элементов.

## Goal

Переделать механику и стили hero-слайдера так, чтобы:

- изображения, тексты, иконки, телефон и CTA-кнопки остались прежними;
- смена слайдов выглядела эффектнее за счет layered background, crossfade, легкого motion и progress-индикатора;
- контент и кнопки не "ползли" при смене слайдов и на адаптивных ширинах;
- исходная папка `static/` не менялась.

## Non-goals

- Не менять тексты, изображения, ссылки, иконки и состав кнопок.
- Не менять backend API, SEO-контракт, sitemap и robots.
- Не добавлять стороннюю библиотеку слайдера.
- Не редактировать `static/`.

## Backend changes

Backend не меняется.

## Frontend changes

- Обновить `frontend/app/components/home/HomeHero.vue`:
  - добавить отдельные фоновые слои для каждого слайда;
  - добавить стабильную рамку для сменяемого текстового блока;
  - добавить автопереключение с паузой при hover/focus и учетом `prefers-reduced-motion`;
  - оставить существующий контент и CTA без изменений.
- Обновить `frontend/app/assets/styles/main.css`:
  - добавить стили фоновых слоев и transition;
  - зафиксировать высоты/ограничения hero-контента;
  - улучшить точки слайдера через progress-индикатор;
  - поправить desktop/mobile размеры, чтобы блок не обрезал кнопки.
- Обновить документацию главной страницы.

## API contract

API-контракт не меняется.

## SEO impact

SEO не меняется: страница остается SSR, основной `h1`, meta, canonical и Open Graph не затрагиваются.

## Tests

- [x] `docker compose exec -T nuxt sh -lc "./node_modules/.bin/vue-tsc --noEmit -p tsconfig.json"`
- [x] `curl.exe -I -k https://rukadobra.localhost/`
- [x] SSR smoke: в HTML главной найдены `home-hero-wrap`, `hero-media-stack`, `hero-copy-frame`.
- [ ] `docker compose exec -T nuxt pnpm lint` — не дошел до анализа кода: отсутствует native binding `@oxc-parser/binding-linux-x64-musl`.
- [ ] `docker compose exec -T nuxt pnpm typecheck` — не дошел до `vue-tsc` из-за той же ошибки `nuxt prepare`.
- [ ] `docker compose exec -T nuxt pnpm build` — не дошел до сборки из-за той же ошибки `@oxc-parser/binding-linux-x64-musl`.

## Acceptance criteria

- [x] Слайды переключаются без изменения существующих текстов, изображений, иконок и кнопок.
- [x] При смене слайдов CTA-кнопки и телефонная строка вынесены из transition-слоя и не зависят от высоты текста.
- [x] Hero-блок получил увеличенную desktop-высоту и фиксированную область copy, чтобы не обрезать кнопки.
- [x] На мобильной ширине добавлены отдельные min-height, padding и переносы для label, note, телефона и кнопок.
- [x] `static/` не редактировалась.

## Postman

Postman collection обновлять не нужно, потому что API endpoints и контракты не меняются.
