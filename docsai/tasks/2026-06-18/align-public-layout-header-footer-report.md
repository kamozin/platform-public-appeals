# Отчет: симметричная сетка публичного layout

## Что сделано

- Базовый `.container` в `frontend/app/assets/styles/main.css` переведен на общий shell width через `--shell-max-width` и `--shell-gutter-current`.
- Добавлена переменная `--shell-gutter-current`, которая переключается на tablet/mobile/small gutters через существующие breakpoint-ы.
- Header/footer переопределения синхронизированы с той же формулой ширины, чтобы не расходиться с основным контентом.
- Главная страница, home-секции и публичные страницы, которые уже используют `.container`, получили единую внешнюю сетку без правок Vue-шаблонов.
- Документация frontend layout обновлена на русском: добавлен раздел про shell-сетку и правило использования `.container`.

## Затронутые файлы

- `frontend/app/assets/styles/main.css`
- `docs/development/frontend-layout.md`
- `docsai/tasks/2026-06-18/align-public-layout-header-footer.md`
- `docsai/tasks/2026-06-18/align-public-layout-header-footer-report.md`

## Проверки

Успешно:

- `docker run --rm -v "\\wsl.localhost\Ubuntu-22.04\home\kamozin\zhaloba\frontend:/app" -w /app node:22-bookworm-slim sh -lc "corepack enable && corepack prepare pnpm@11.7.0 --activate && pnpm typecheck"`
- `docker run --rm -v "\\wsl.localhost\Ubuntu-22.04\home\kamozin\zhaloba\frontend:/app" -w /app node:22-bookworm-slim sh -lc "corepack enable && corepack prepare pnpm@11.7.0 --activate && pnpm lint"`
- `docker run --rm -v "\\wsl.localhost\Ubuntu-22.04\home\kamozin\zhaloba\frontend:/app" -w /app node:22-bookworm-slim sh -lc "corepack enable && corepack prepare pnpm@11.7.0 --activate && pnpm build"`
- SSR smoke через поднятый compose-стенд:
  - `https://rukadobra.localhost/` содержит `home-hero-wrap`, `site-header`, `site-footer`;
  - `https://rukadobra.localhost/book` содержит `content-page`, `site-header`, `site-footer`;
  - `https://rukadobra.localhost/appeals` содержит `content-page`, `appeals-page-title`, `site-footer`;
  - `https://rukadobra.localhost/news` содержит `content-page`, `news-page-title`, `site-footer`.

Примечания:

- `nuxt build` прошел с существующим предупреждением Nuxt/Vite про sourcemap `nuxt:module-preload-polyfill`.
- In-app browser через `browser-use` не использовался: в этой сессии не удалось получить Node REPL `js` tool через discovery. Проверка выполнена через build, prerender/SSR smoke и доступный compose URL.

## Acceptance criteria

- [x] Header, main-контент и footer используют согласованные `max-width` и боковые отступы.
- [x] Главная страница визуально выравнивается по той же сетке через общий `.container`.
- [x] Остальные публичные страницы, использующие `.container`, не выбиваются из общей shell-сетки.
- [x] SSR и SEO meta публичных страниц не ухудшены.
- [x] `static/` не изменялась.
- [x] Backend и API-контракты не изменялись.

## Postman

Postman collection не обновлялась: API endpoints и контракты не менялись.
