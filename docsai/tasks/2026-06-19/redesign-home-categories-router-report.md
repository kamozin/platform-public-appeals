# Отчёт: новый вариант блока выбора направления

## Что сделано

Блок "Куда направить обращение или жалобу" на главной странице переделан в формат маршрутизатора:

- вместо сетки одинаковых карточек добавлена двухзонная композиция;
- частые темы `ЖКХ`, `Дороги` и `Транспорт` вынесены в акцентную левую зону;
- остальные направления показаны компактным списком справа;
- повторяющиеся крупные кнопки заменены на более спокойные действия и стрелки;
- мобильная версия складывается в одну колонку.

## Затронутые файлы

- `frontend/app/components/home/HomeCategoriesPreview.vue`
- `frontend/app/assets/styles/main.css`
- `docs/development/frontend-home-page.md`
- `docsai/tasks/2026-06-19/redesign-home-categories-router.md`
- `docsai/tasks/2026-06-19/redesign-home-categories-router-report.md`

## Backend

Backend не менялся.

## API contract

API-контракт не менялся.

## SEO

Страница `/` осталась SSR-страницей. Тексты категорий и ссылки на форму обращения остаются в серверном HTML.

## Проверки

Выполнено:

```bash
docker compose exec nuxt sh -lc "corepack pnpm typecheck"
docker compose exec nuxt sh -lc "corepack pnpm lint"
docker compose exec nuxt sh -lc "corepack pnpm build"
curl.exe -k -s https://rukadobra.localhost/
git diff --check -- frontend/app/components/home/HomeCategoriesPreview.vue frontend/app/assets/styles/main.css docs/development/frontend-home-page.md docsai/tasks/2026-06-19/redesign-home-categories-router.md docsai/tasks/2026-06-19/redesign-home-categories-router-report.md
```

`typecheck`, `lint` и `build` прошли успешно. Во время `build` Nuxt вывел предупреждение про sourcemap для `nuxt:module-preload-polyfill`, сборку оно не остановило.

SSR smoke подтвердил наличие новой разметки:

- `category-router`;
- `Начните с понятной темы`;
- `Остальные направления`;
- `Транспорт`.

Визуально проверено:

- desktop: 1530px ширина;
- mobile: 390px ширина.

## Postman

Postman collection не обновлялась, потому что API endpoints не добавлялись и не менялись.
