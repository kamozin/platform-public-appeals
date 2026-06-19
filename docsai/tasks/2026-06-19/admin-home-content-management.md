# Управление рекламой, слайдером и категориями главной страницы

## Context

Публичный контент категорий, новостей и обращений уже хранится в БД и управляется через admin API.
Но главная страница всё ещё содержит статические массивы:

- hero-слайдер в `HomeHero.vue`;
- рекламные баннеры в `HomeAdBanner.vue`;
- превью категорий в `HomeCategoriesPreview.vue`.

Из-за этого админка не управляет всей главной страницей, а часть контента остаётся в коде.

## Goal

Сделать управление главной страницей из БД и админки:

- добавить хранение hero-слайдов;
- добавить хранение рекламных баннеров;
- добавить public API для данных главной страницы;
- добавить admin API для CRUD hero-слайдов и рекламы;
- расширить Nuxt-админку вкладками `Слайдер` и `Реклама`;
- подключить `HomeHero.vue`, `HomeAdBanner.vue` и `HomeCategoriesPreview.vue` к API;
- обновить сидер первичного наполнения базы.

## Non-goals

- Не трогать исходную папку `static/`.
- Не добавлять Laravel web UI, Blade, Inertia или Livewire.
- Не добавлять загрузку файлов; изображения и видео задаются URL-ами.
- Не менять текущий CRUD категорий, кроме использования категорий на главной.
- Не строить отдельный рекламный трекинг кликов/показов.

## Backend changes

- Добавить таблицу `homepage_slides`.
- Добавить таблицу `advertisements`.
- Добавить модели `HomepageSlide` и `Advertisement`.
- Расширить `PublicContentService` методом `home()`.
- Добавить `GET /api/v1/home`.
- Добавить admin endpoints:
  - `GET /api/v1/admin/homepage-slides`
  - `POST /api/v1/admin/homepage-slides`
  - `PATCH /api/v1/admin/homepage-slides/{id}`
  - `DELETE /api/v1/admin/homepage-slides/{id}`
  - `GET /api/v1/admin/advertisements`
  - `POST /api/v1/admin/advertisements`
  - `PATCH /api/v1/admin/advertisements/{id}`
  - `DELETE /api/v1/admin/advertisements/{id}`

## Frontend changes

- Добавить типы public home DTO.
- Добавить `useHomeContent()`.
- Передавать данные home API в:
  - `HomeHero.vue`;
  - `HomeAdBanner.vue`;
  - `HomeCategoriesPreview.vue`.
- Расширить `useAdminContent()` методами управления слайдами и рекламой.
- Расширить `/admin` вкладками `Слайдер` и `Реклама`.

## API contract

`GET /api/v1/home`:

```json
{
  "data": {
    "slides": [],
    "advertisements": [],
    "categoryGroups": [],
    "seo": {}
  }
}
```

Публично возвращаются только активные слайды, активные рекламные баннеры в актуальном периоде и активные категории.

## SEO impact

Главная страница остаётся SSR/prerender страницей.
Основной контент слайдера и категорий должен приходить на сервере через `useAsyncData`, чтобы HTML главной не был пустым до hydration.

## Tests

- Backend feature tests:
  - `GET /api/v1/home`;
  - admin CRUD для слайдов;
  - admin CRUD для рекламы;
  - route names.
- Frontend:
  - `docker compose exec -T nuxt pnpm typecheck`;
  - `docker compose exec -T nuxt pnpm lint`.

## Acceptance criteria

- Главный слайдер берёт данные из API/БД.
- Реклама на главной берёт данные из API/БД.
- Превью категорий на главной берёт категории из API/БД.
- Admin может создать, обновить и удалить слайд.
- Admin может создать, обновить и удалить рекламный баннер.
- Сидер создаёт начальные слайды, рекламные баннеры и категории.
- В новых frontend-компонентах нет прямого хардкода `/api/v1`.
- Проверки backend/frontend проходят.

## Postman

Нужно обновить collection:

- добавить `GET /home`;
- добавить admin CRUD для `homepage-slides`;
- добавить admin CRUD для `advertisements`.
