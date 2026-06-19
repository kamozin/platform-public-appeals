# Главная страница Nuxt

## Назначение

Главная страница `/` перенесена из `static/index.html` в Nuxt SSR. Управляемый контент слайдера, рекламы и категорий приходит из `GET /api/v1/home` и рендерится на сервере без ожидания client-side JavaScript.

## Состав страницы

Основной файл:

- `frontend/app/pages/index.vue`
- `frontend/app/composables/useHomeContent.ts`

Компоненты:

- `frontend/app/components/home/HomeHero.vue` — hero slider из БД.
- `frontend/app/components/home/HomeSupportVideo.vue` — блок видео поддержки.
- `frontend/app/components/home/HomeTopPanels.vue` — народная жалобная книга и география работы.
- `frontend/app/components/home/HomeAdBanner.vue` — рекламный баннер из БД.
- `frontend/app/components/home/HomeStatsStrip.vue` — показатели платформы.
- `frontend/app/components/home/HomeCategoriesPreview.vue` — категории обращений из БД.
- `frontend/app/components/home/HomeHowItWorks.vue` — шаги подачи обращения.
- `frontend/app/components/home/HomeAppealsPreview.vue` — статичная preview-лента обращений.
- `frontend/app/components/home/HomeLatestNews.vue` — статичный preview-блок новостей.
- `frontend/app/components/home/HomeTransparencyTrust.vue` — прозрачность и trust-блоки.

## Конверсионные блоки

Блоки статистики, категорий и сценария подачи обращения усилены как основная зона принятия решения:

- `HomeStatsStrip.vue` показывает крупные показатели, короткие пояснения и CTA на создание обращения.
- `HomeCategoriesPreview.vue` оформлен как маршрутизатор обращения: частые темы вынесены в отдельную акцентную зону, остальные направления показаны компактным списком без сетки одинаковых карточек.
- `HomeHowItWorks.vue` сокращён до 4 крупных шагов с пояснениями, преимуществами и заметной кнопкой начала обращения.

Статистика и сценарий подачи обращения остаются локальными SSR-компонентами. Категории на главной берутся из API, чтобы админка управляла составом направлений без изменения frontend-кода.

## Hero-слайдер

Hero-слайдер на главной странице реализован в `frontend/app/components/home/HomeHero.vue`.

Особенности текущей реализации:

- тексты, изображения и CTA-кнопки приходят из `homepage_slides`;
- фон каждого слайда рендерится отдельным слоем, чтобы переходы шли через crossfade без скачка layout;
- сменяемая текстовая часть находится в фиксированной области, а телефон и CTA-кнопки вынесены из transition-слоя;
- автопереключение ставится на паузу при hover/focus и не запускается при `prefers-reduced-motion: reduce`;
- стили и адаптивные ограничения находятся в `frontend/app/assets/styles/main.css`.

## Данные

`frontend/app/pages/index.vue` вызывает `useHomeContent().fetchHome()` через `useAsyncData`.

Контракт `GET /api/v1/home` отдаёт:

- `slides` — активные записи `homepage_slides`;
- `advertisements` — активные записи `advertisements` placement `home_promo` с учётом периода показа;
- `categoryGroups` — активные группы и категории;
- `seo` — title, description, canonical, robots и Open Graph image.

Admin-пользователь управляет данными главной на странице `/admin` во вкладках:

- `Категории`;
- `Слайдер`;
- `Реклама`.

## SEO

Страница индексируемая:

- один основной `h1`;
- `title`;
- `description`;
- canonical URL;
- Open Graph title, description, URL и image.

## Проверки

```bash
docker compose run --rm nuxt sh -lc "corepack enable && corepack prepare pnpm@11.7.0 --activate && pnpm typecheck && pnpm lint && pnpm build"
```

SSR smoke:

```bash
curl -k https://rukadobra.localhost/
```

В HTML должны быть `h1`, meta description, canonical, `data-ssr="true"` и основные секции главной.
