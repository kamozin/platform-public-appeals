# Отчёт по задаче 06: Content pages for legal and complaint book

## Что сделано

- Добавлены Nuxt SSR pages:
  - `/privacy`;
  - `/agreement`;
  - `/book`.
- Для `/privacy` и `/agreement` добавлены базовые тексты-заглушки с явной пометкой о необходимости юридического согласования.
- Для `/book` добавлена публичная статическая страница народной жалобной книги.
- Настроены SEO meta и canonical для всех трёх страниц.
- Ссылки footer на `/privacy` и `/agreement` переведены на реальные Nuxt routes.
- Ссылка с главной на `/book` переведена на реальный Nuxt route.
- Добавлены общие стили content pages в `frontend/app/assets/styles/main.css`.
- Создана документация `docs/development/frontend-content-pages.md`.

## Затронутые файлы

- `frontend/app/pages/privacy.vue`
- `frontend/app/pages/agreement.vue`
- `frontend/app/pages/book.vue`
- `frontend/app/components/layout/AppFooter.vue`
- `frontend/app/components/home/HomeTopPanels.vue`
- `frontend/app/assets/styles/main.css`
- `docs/development/frontend-content-pages.md`

## Проверки

- `docker compose run --rm nuxt sh -lc "corepack enable && corepack prepare pnpm@11.7.0 --activate && pnpm typecheck && pnpm lint && pnpm build"` — прошёл.
- `docker compose restart nuxt` — dev-сервер перезапущен.
- `curl -k https://rukadobra.localhost/privacy` — `200 OK`, есть `h1`, `title`, canonical, `data-ssr="true"` и пометка `Текст-заглушка`.
- `curl -k https://rukadobra.localhost/agreement` — `200 OK`, есть `h1`, `title`, canonical, `data-ssr="true"` и пометка `Текст-заглушка`.
- `curl -k https://rukadobra.localhost/book` — `200 OK`, есть `h1`, `title`, canonical, `data-ssr="true"` и пометка `Предварительная публичная страница`.
- SSR HTML главной содержит ссылки на `/privacy`, `/agreement`, `/book`.
- SSR HTML главной не содержит `Vue Router warn`.

## Postman

Postman collection не обновлялась, потому что задача не добавляет и не меняет backend API endpoints.

## Примечания

- `static/` не изменялась.
- Юридические тексты являются временными и требуют отдельного согласования.
- `pnpm build` завершился успешно, но Nuxt/Vite вывел предупреждение про sourcemap плагина `nuxt:module-preload-polyfill`; это не ошибка приложения.
