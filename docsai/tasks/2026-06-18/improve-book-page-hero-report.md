# Отчёт: улучшение hero-блока страницы жалобной книги

## Что сделано

- Обновлён первый экран страницы `/book`: текстовый блок стал компактнее, обложка книги больше не обрезается и не прижимается к правому краю.
- Добавлены CTA-ссылки `Смотреть обращения` и `Подать обращение`.
- Для `.book-hero` добавлены отдельные desktop/mobile стили, не меняющие общий `content-hero` остальных страниц.
- Обновлена документация по статическим content pages.

## Затронутые файлы

- `frontend/app/pages/book.vue`
- `frontend/app/assets/styles/main.css`
- `docs/development/frontend-content-pages.md`
- `docsai/tasks/2026-06-18/improve-book-page-hero.md`

## Проверки

- [x] SSR smoke: `curl.exe -k -s https://rukadobra.localhost/book` отдаёт `book-hero`, `book-hero-actions`, `Смотреть обращения`, `Подать обращение`.
- [x] Визуальная проверка Edge headless, desktop viewport `1530x827`.
- [x] Визуальная проверка Edge headless, mobile viewport `390x844`.
- [x] `git diff --check -- frontend/app/pages/book.vue frontend/app/assets/styles/main.css docs/development/frontend-content-pages.md docsai/tasks/2026-06-18/improve-book-page-hero.md`
- [ ] `pnpm typecheck`, `pnpm lint`, `nuxt build` не выполнены: доступный shell не видит корректное frontend Node/pnpm окружение, а `docker compose exec nuxt` в текущем состоянии показывает Node `v12.22.9` и не даёт запустить Nuxt CLI из `frontend/node_modules`.

## Postman

Postman collection обновлять не нужно, потому что API endpoints и контракты не менялись.
