# Отчёт: переделать визуальный стиль круглых иконок и бейджей во frontend

## Что сделано

- Обновлён общий визуальный паттерн маленьких icon badge во frontend: тонкая рамка, мягкая светлая подложка, единый радиус и лёгкая глубина.
- Исправлена проблема в личном кабинете, из-за которой текстовые бейджи `прочитано`, `новое`, `получено`, `в процессе` могли наследовать стили круглой иконки через слишком широкий селектор `> span`.
- Бейджи статусов приведены к компактному chip-стилю с отдельными вариантами `blue`, `green`, `gray`, `orange`, `red`.
- Обновлены похожие паттерны в категориях, форме обращения, личном кабинете и админке.
- Для непрочитанных уведомлений добавлен аккуратный левый индикатор, чтобы состояние не зависело только от цвета бейджа.
- На мобильной ширине статус уведомления размещается в текстовой колонке и не конфликтует с иконкой.

## Затронутые файлы

- `frontend/app/assets/styles/main.css`
- `docsai/tasks/2026-06-19/redesign-frontend-icon-badges.md`
- `docsai/tasks/2026-06-19/redesign-frontend-icon-badges-report.md`

## Backend

Не менялся.

## Frontend

Изменения касаются только CSS:

- `ready-badge` и `status-pill`;
- cabinet icon containers и `cabinet-pill`;
- список уведомлений в личном кабинете;
- achievement badges;
- category icon blocks;
- all-category icon blocks;
- admin icon buttons и admin status pills.

## API contract

Не менялся.

## SEO

Не менялось. SSR/route rules/meta не затронуты.

## Postman

Не требуется, так как API endpoint-ы не менялись.

## Проверки

- `docker compose exec -T nuxt pnpm lint` — успешно.
- `docker compose exec -T nuxt pnpm typecheck` — успешно.
- `curl -k -I https://rukadobra.localhost/dashboard` — `200 OK`.
- `curl -k -I https://rukadobra.localhost/categories` — `200 OK`.
- `curl -k -I https://rukadobra.localhost/appeal/new` — `200 OK`.
- `curl -k -I https://rukadobra.localhost/admin` — `200 OK`.

## Примечания

Локальный запуск через WSL/Windows Node не использовался для финальной проверки: в WSL установлен Node 12, а Windows Corepack не смог получить pnpm из-за ошибки проверки подписи. Проверки выполнены в уже запущенном Docker-контейнере `nuxt` на Node 22.
