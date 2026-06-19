# Отчёт: исправлено наложение desktop-хедера

## Что сделано

- Добавлена отдельная ширина shell chrome `--shell-chrome-max-width: 1382px`.
- Header/footer shell-контейнеры переведены на эту ширину, чтобы desktop-хедеру хватало места для логотипа, навигации и правых действий.
- Breakpoint для `.shell-header` поднят до `1400px`, чтобы на промежуточных ширинах хедер переходил в hamburger-меню до появления наложений.

## Затронутые файлы

- `frontend/app/assets/styles/main.css`
- `docsai/tasks/2026-06-19/fix-header-desktop-overlap.md`
- `docsai/tasks/2026-06-19/fix-header-desktop-overlap-report.md`

## Backend

Backend не менялся.

## Postman

Не требуется, API-контракт не менялся.

## Проверки

Выполнено:

```bash
docker compose exec -T nuxt pnpm typecheck
docker compose exec -T nuxt pnpm lint
docker compose exec -T nuxt pnpm build
```

Результат: успешно.

Визуальная проверка:

- headless Edge, viewport `1446x260`;
- гостевое состояние: навигация не накладывается на логотип;
- авторизованное admin-состояние: `Админка Подать обращение Выйти`;
- DOM-метрика после hydration: первый пункт меню начинается на `66px` правее правого края логотипа.

## Примечания

При `pnpm build` Nuxt/Vite вывел предупреждение про sourcemap плагина `nuxt:module-preload-polyfill`; сборка завершилась успешно.
