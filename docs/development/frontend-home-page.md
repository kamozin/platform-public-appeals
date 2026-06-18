# Главная страница Nuxt

## Назначение

Главная страница `/` перенесена из `static/index.html` в Nuxt SSR. Контент страницы рендерится на сервере и доступен в HTML без ожидания client-side JavaScript.

## Состав страницы

Основной файл:

- `frontend/app/pages/index.vue`

Компоненты:

- `frontend/app/components/home/HomeHero.vue` — hero slider.
- `frontend/app/components/home/HomeSupportVideo.vue` — блок видео поддержки.
- `frontend/app/components/home/HomeTopPanels.vue` — народная жалобная книга и география работы.
- `frontend/app/components/home/HomeAdBanner.vue` — рекламный баннер.
- `frontend/app/components/home/HomeStatsStrip.vue` — показатели платформы.
- `frontend/app/components/home/HomeCategoriesPreview.vue` — категории обращений.
- `frontend/app/components/home/HomeHowItWorks.vue` — шаги подачи обращения.
- `frontend/app/components/home/HomeAppealsPreview.vue` — статичная preview-лента обращений.
- `frontend/app/components/home/HomeLatestNews.vue` — статичный preview-блок новостей.
- `frontend/app/components/home/HomeTransparencyTrust.vue` — прозрачность и trust-блоки.

## Данные

В этой задаче backend API не добавлялся. Блоки главной используют статические frontend mock data внутри компонентов. Динамические API-контракты для категорий, новостей и обращений будут добавляться отдельными атомарными задачами.

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
