# Отчёт по задаче 05: Frontend public home page

## Что сделано

- Главная страница `/` перенесена из `static/index.html` в Nuxt SSR.
- Убран временный health-smoke контент с главной.
- Добавлены home-компоненты:
  - `HomeHero`;
  - `HomeSupportVideo`;
  - `HomeTopPanels`;
  - `HomeAdBanner`;
  - `HomeStatsStrip`;
  - `HomeCategoriesPreview`;
  - `HomeHowItWorks`;
  - `HomeAppealsPreview`;
  - `HomeLatestNews`;
  - `HomeTransparencyTrust`.
- Hero slider реализован SSR-safe: первый слайд присутствует в серверном HTML, переключение работает на клиенте.
- Общий SVG sprite расширен иконками главной страницы.
- Нужные изображения главной скопированы из `static/assets` в `frontend/public/assets`.
- Добавлены стили главной в `frontend/app/assets/styles/main.css`.
- Настроены SEO meta для `/`: title, description, canonical и Open Graph image.
- Внутренние ссылки переведены с `.html` на Nuxt URL. Будущие маршруты помечены как `external`, пока их pages будут реализованы отдельными задачами.
- Создана документация `docs/development/frontend-home-page.md`.

## Затронутые файлы

- `frontend/app/pages/index.vue`
- `frontend/app/components/home/HomeHero.vue`
- `frontend/app/components/home/HomeSupportVideo.vue`
- `frontend/app/components/home/HomeTopPanels.vue`
- `frontend/app/components/home/HomeAdBanner.vue`
- `frontend/app/components/home/HomeStatsStrip.vue`
- `frontend/app/components/home/HomeCategoriesPreview.vue`
- `frontend/app/components/home/HomeHowItWorks.vue`
- `frontend/app/components/home/HomeAppealsPreview.vue`
- `frontend/app/components/home/HomeLatestNews.vue`
- `frontend/app/components/home/HomeTransparencyTrust.vue`
- `frontend/app/components/layout/AppIcon.vue`
- `frontend/app/components/layout/AppSvgSprite.vue`
- `frontend/app/assets/styles/main.css`
- `frontend/public/assets/hero-civic-flag.png`
- `frontend/public/assets/hero-legal-consultation.png`
- `frontend/public/assets/hero-community-meeting.png`
- `frontend/public/assets/hero-hands-heart.png`
- `frontend/public/assets/complaint-book-cover-clean.png`
- `frontend/public/assets/geo-russia-map.png`
- `frontend/public/assets/114a584a-17bb-4ecb-95fd-c338df16704e.png`
- `frontend/public/assets/issue-entrance-roof.png`
- `frontend/public/assets/issue-road.png`
- `frontend/public/assets/issue-sport-roof.png`
- `docs/development/frontend-home-page.md`

## Проверки

- `docker compose run --rm nuxt sh -lc "corepack enable && corepack prepare pnpm@11.7.0 --activate && pnpm typecheck && pnpm lint && pnpm build"` — прошёл.
- `docker compose restart nuxt` — dev-сервер перезапущен.
- `curl -k https://rukadobra.localhost/` — `200 OK`.
- SSR HTML содержит:
  - `h1`;
  - `Вместе решаем проблемы людей и защищаем их права`;
  - title `Рука добра - платформа обращений и жалоб граждан`;
  - meta description;
  - canonical `https://rukadobra.localhost/`;
  - OG image `https://rukadobra.localhost/assets/hero-civic-flag.png`;
  - `data-ssr="true"`;
  - блоки категорий и последних новостей.
- SSR HTML не содержит `.html` ссылок и `Vue Router warn`.

## Postman

Postman collection не обновлялась, потому что задача не добавляет и не меняет backend API endpoints.

## Примечания

- `static/` не изменялась.
- Playwright в frontend-зависимостях пока не добавлен, поэтому screenshot smoke на desktop/mobile viewport не запускался.
- `pnpm build` завершился успешно, но Nuxt/Vite вывел предупреждение про sourcemap плагина `nuxt:module-preload-polyfill`; это не ошибка приложения.
