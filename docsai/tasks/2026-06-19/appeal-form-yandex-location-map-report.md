# Отчёт: Яндекс.Карта для места происшествия

## Что сделано

- Статический мокап карты в `/appeal/new` заменён на клиентский компонент `AppealYandexLocationMap`.
- Компонент подключает Yandex Maps JavaScript API 2.1 в браузере через `NUXT_PUBLIC_YANDEX_MAPS_API_KEY`.
- Поле адреса синхронизировано с картой:
  - ввод адреса двигает карту и маркер через геокодирование;
  - клик по карте обновляет маркер и адрес через обратное геокодирование;
  - кнопка геолокации браузера выбирает текущую точку при разрешении пользователя.
- Добавлены состояния загрузки, ошибки и отсутствующего API-ключа.
- Backend-контракт не менялся: в API отправляется только строка `location`, координаты не сохраняются.

## Затронутые файлы

- `frontend/app/components/appeal/YandexLocationMap.client.vue`
- `frontend/app/pages/appeal/new.vue`
- `frontend/app/assets/styles/main.css`
- `frontend/nuxt.config.ts`
- `.env.example`
- `docker-compose.yml`
- `docs/development/frontend-private-workflows.md`
- `docsai/tasks/2026-06-19/appeal-form-yandex-location-map.md`
- `docsai/tasks/2026-06-19/appeal-form-yandex-location-map-report.md`

## Проверки

Выполнено:

- `git diff --check` для затронутых файлов;
- `docker compose run --rm nuxt corepack pnpm typecheck`;
- `docker compose run --rm nuxt corepack pnpm lint`;
- `curl.exe -k -I https://rukadobra.localhost/appeal/new` → `200 OK`.

Ограничения окружения:

- в текущем `.env` не задан `NUXT_PUBLIC_YANDEX_MAPS_API_KEY`, поэтому локально компонент показывает fallback до добавления ключа;
- прямой запуск Nuxt-проверок вне Docker недоступен: Windows `pnpm` отсутствует, Corepack падает на проверке подписи, WSL содержит Node `12.22.9`.

## Postman

Postman collection не обновлялась: API endpoints и payload contract не изменились.
