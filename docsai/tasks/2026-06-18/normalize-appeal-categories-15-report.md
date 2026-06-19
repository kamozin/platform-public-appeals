# Нормализация справочника категорий обращений до 15 категорий — отчёт

## Что сделано

- `GET /api/v1/categories` приведён к единому справочнику из 15 категорий.
- Добавлена четвёртая группа категорий `services`.
- Зафиксированы стабильные slug-и категорий:
  - `zhkh`
  - `roads`
  - `transport`
  - `improvement`
  - `healthcare`
  - `education`
  - `social-support`
  - `ecology`
  - `safety`
  - `public-services`
  - `land-real-estate`
  - `authorities`
  - `telecom`
  - `commerce-services`
  - `other`
- Backend feature test категорий теперь проверяет 4 группы, 15 категорий, порядок slug-ов и уникальность slug-ов.
- Страница `/categories` расширила whitelist иконок под новый справочник.
- Мастер `/appeal/new` получает категории из `GET /api/v1/categories` и больше не держит отдельный frontend-справочник.
- При неизвестном `?category=<slug>` форма выбирает первую категорию из API-ответа.
- Блок категорий на главной переведён на slug-и из нового справочника.
- Документация публичного API и frontend-страниц обновлена на русском.
- Postman collection дополнена примером ответа `GET /api/v1/categories` с 15 категориями.

## Затронутые файлы

- `app/Application/PublicContent/PublicContentService.php`
- `tests/Feature/Api/CategoriesEndpointTest.php`
- `frontend/app/pages/categories.vue`
- `frontend/app/pages/appeal/new.vue`
- `frontend/app/components/home/HomeCategoriesPreview.vue`
- `docs/development/public-content-api.md`
- `docs/development/frontend-public-content-pages.md`
- `docsai/postmancollection/rukadobra-api.postman_collection.json`
- `docsai/tasks/2026-06-18/normalize-appeal-categories-15.md`
- `docsai/tasks/2026-06-18/normalize-appeal-categories-15-report.md`

## Проверки

- `php artisan test tests/Feature/Api/CategoriesEndpointTest.php` — passed.
- `vendor/bin/pint --test` — passed.
- `vendor/bin/phpstan analyse --memory-limit=1G` — passed.
- `ConvertFrom-Json` для `docsai/postmancollection/rukadobra-api.postman_collection.json` — passed.
- Поиск устаревших frontend/backend-ссылок `category=<old-slug>` — совпадений нет.

## Ограничения проверок

- `php artisan test` полностью запускался, но часть тестов упала из-за окружения: в WSL PHP отсутствует `pdo_sqlite` для `sqlite::memory:`. Тесты категорий при этом проходят.
- `pnpm typecheck` и `pnpm lint` не удалось запустить в текущем окружении:
  - `pnpm` не установлен в WSL;
  - WSL Node имеет версию `12.22.9` и не поддерживает синтаксис Nuxt CLI;
  - Windows Node `20.16.0` доступен, но не может корректно пройти pnpm symlink-структуру внутри WSL UNC-пути.

## Postman

- Обновлён запрос `Public Content / Categories Index`.
- Добавлен пример `200 Categories Dictionary` с 15 категориями.
