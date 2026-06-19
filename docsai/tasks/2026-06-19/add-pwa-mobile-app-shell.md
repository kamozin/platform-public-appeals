# PWA shell мобильного приложения

## Context

Frontend сейчас работает как Nuxt 4 SSR-сайт без PWA-настроек: в `frontend/package.json` нет PWA-зависимостей, в `frontend/nuxt.config.ts` нет manifest/service worker, а публичные страницы используют обычные site layout-ы с header/footer.

Нужно добавить PWA-версию, которая после установки открывается не как мобильная версия сайта, а как отдельный мобильный app-like интерфейс. Публичный SEO-сайт при этом должен остаться SSR-first и не должен превращаться в SPA.

## Goal

Добавить MVP PWA:

- installable web app с manifest, иконками и standalone-режимом;
- отдельный entrypoint `/app` как `start_url`;
- отдельный Nuxt layout для PWA без обычного site header/footer;
- мобильный app shell с верхней панелью, нижней навигацией, safe-area и touch-first интерфейсом;
- базовый service worker для кеширования app shell-а и offline fallback;
- noindex для `/app` и всех app-only экранов.

## Non-goals

- Не добавлять push-уведомления.
- Не добавлять offline-черновики обращений и фоновую синхронизацию отправки.
- Не добавлять Capacitor / native wrapper / публикацию в App Store или Google Play.
- Не переносить весь личный кабинет в новый app layout.
- Не менять backend API.
- Не менять публичные SEO-страницы и их SSR/SWR-логику.
- Не править `static/`.
- Не добавлять Laravel UI / Blade / Inertia / Livewire.

## Backend changes

Backend не изменяется.

Laravel остаётся API-only. Новые endpoints для PWA MVP не нужны.

## Frontend changes

- Проверить совместимость `@vite-pwa/nuxt` с текущим `nuxt: ^4.4.8`.
- Если совместимость подтверждается, добавить `@vite-pwa/nuxt` в `frontend/package.json` и подключить модуль в `frontend/nuxt.config.ts`.
- Если модуль несовместим с текущей сборкой Nuxt 4, реализовать минимальный manifest + service worker без стороннего PWA-модуля и зафиксировать причину в отчёте.
- Настроить PWA manifest:
  - `name`;
  - `short_name`;
  - `description`;
  - `lang: ru`;
  - `scope: /`;
  - `start_url: /app`;
  - `display: standalone`;
  - `theme_color`;
  - `background_color`;
  - `icons` с обычными и maskable PNG.
- Добавить PWA-иконки в `frontend/public`.
- Добавить Apple-compatible meta/link-настройки для Home Screen:
  - `apple-mobile-web-app-title`;
  - `apple-mobile-web-app-capable`;
  - `apple-mobile-web-app-status-bar-style`;
  - `apple-touch-icon`.
- Добавить route rules для app-only интерфейса:
  - `/app`: client-only или SSR-safe shell без SEO-контента;
  - `/app/**`: client-only или SSR-safe shell без SEO-контента;
  - `X-Robots-Tag: noindex, nofollow`.
- Создать `frontend/app/layouts/pwa.vue`:
  - без `LayoutAppHeader`;
  - без `LayoutAppFooter`;
  - с `LayoutAppSvgSprite`;
  - с контейнером на `100dvh`;
  - с поддержкой `env(safe-area-inset-*)`;
  - с app-like scroll behavior.
- Создать стартовый экран `frontend/app/pages/app/index.vue` с `definePageMeta({ layout: 'pwa' })` и `useNoindexSeo()`.
- Добавить PWA-компоненты:
  - app bar;
  - bottom navigation;
  - offline banner;
  - install prompt для браузеров, которые поддерживают `beforeinstallprompt`;
  - update prompt для новой версии service worker.
- Добавить CSS для PWA shell-а:
  - фиксированная нижняя навигация;
  - корректные safe-area отступы;
  - touch-targets не меньше 44px;
  - отсутствие обычных desktop/site паттернов;
  - отдельные стили для `@media (display-mode: standalone)`.
- Подключать все API-запросы только через текущий `useApi()`, без прямого хардкода `/api/v1`.

## API contract

Новые API endpoints не добавляются.

PWA MVP может ссылаться на существующие маршруты и данные, но не меняет backend contract.

Если стартовый экран будет показывать существующие данные, использовать текущие endpoints:

- `GET /api/v1/public-content/home`
- `GET /api/v1/appeals`
- `GET /api/v1/dashboard`, только для авторизованного состояния при наличии уже существующего frontend-потока

Ошибки обрабатываются через существующий формат API error response.

## SEO impact

Публичные SEO-страницы остаются SSR/SWR/prerender по текущим правилам.

`/app` и `/app/**` не должны индексироваться:

- `X-Robots-Tag: noindex, nofollow`;
- `useNoindexSeo()` на app-only страницах;
- без sitemap-включения для app-only routes.

Manifest подключается глобально, но `start_url` ведёт на `/app`, чтобы установленная PWA открывала мобильный app shell, а не обычную главную сайта.

## Tests

Проверить:

- `docker compose exec -T nuxt pnpm install`, если добавлена новая зависимость;
- `docker compose exec -T nuxt pnpm typecheck`;
- `docker compose exec -T nuxt pnpm lint`;
- `docker compose exec -T nuxt pnpm build`;
- Chrome DevTools Application: manifest валиден, service worker зарегистрирован, icons читаются;
- Lighthouse PWA audit для production build/preview;
- ручная установка на Android Chrome;
- ручное добавление на Home Screen в iOS Safari;
- запуск установленной PWA открывает `/app`;
- standalone-режим не показывает обычный site header/footer;
- offline mode показывает fallback, а не пустой экран;
- в коде нет прямого хардкода `/api/v1`.

## Acceptance criteria

- [ ] PWA устанавливается в поддерживаемых браузерах.
- [ ] Manifest валиден и содержит `start_url: /app`, `display: standalone`, корректные иконки и цвета.
- [ ] После запуска с Home Screen открывается `/app`, а не публичная главная сайта.
- [ ] `/app` визуально выглядит как мобильное приложение: отдельный app bar, нижняя навигация, full-height shell, safe-area, без site header/footer.
- [ ] `/app` и `/app/**` закрыты от индексации.
- [ ] Service worker зарегистрирован и отдаёт offline fallback при отсутствии сети.
- [ ] Публичные SSR/SEO-страницы не переведены в SPA и не потеряли текущие route rules.
- [ ] Backend не изменён.
- [ ] `static/` не изменён.
- [ ] TypeScript typecheck и Nuxt build проходят.

## Postman

Postman collection не требуется обновлять, потому что новые API endpoints не добавляются и backend contract не меняется.
