# Отчет по задаче 02: Backend API foundation

## Что сделано

- Установлен Laravel skeleton в корень монорепозитория.
- Backend переведен в API-only режим:
  - `bootstrap/app.php` подключает `routes/api.php`;
  - web route с Blade `welcome` удален;
  - API prefix `/api` задается Laravel bootstrap-ом, внутри `routes/api.php` используется только `v1`.
- Добавлен endpoint `GET /api/v1/health` с именем route `api.health`.
- Добавлен единый JSON error contract через `App\Support\Api\ApiErrorResponseFactory`.
- Добавлены базовые тексты API-ошибок в `lang/en/api_errors.php`.
- Подключены и настроены:
  - Pest;
  - Laravel Pint;
  - PHPStan + Larastan.
- Обновлены Docker env defaults для Laravel (`APP_KEY`, locale, cache/queue/session).
- Создана dev-документация: `docs/development/backend-api.md`.
- Создана Postman collection: `docsai/postmancollection/rukadobra-api.postman_collection.json`.

## Контракт health endpoint

Request:

```http
GET /api/v1/health
Accept: application/json
```

Response `200`:

```json
{
  "data": {
    "status": "ok"
  }
}
```

## Контракт ошибки

Пример для отсутствующего route:

```json
{
  "error": {
    "code": "NOT_FOUND",
    "message": "Resource not found.",
    "details": {},
    "trace_id": "trace-smoke"
  }
}
```

## Проверки

- `docker compose exec -T laravel composer validate --strict` — прошел.
- `docker compose exec -T laravel composer test` — прошел, 3 теста / 14 assertions.
- `docker compose exec -T laravel composer lint` — прошел, Pint + PHPStan/Larastan без ошибок.
- `curl -k https://rukadobra.localhost/api/v1/health` — `200 OK`.
- `curl -k https://rukadobra.localhost/api/v1/missing -H "X-Request-Id: trace-smoke"` — `404`, единый JSON error contract.

## Примечания

- Локальная `.env` создана из `.env.example` и ключ приложения сгенерирован через `php artisan key:generate --force`.
- Файлы `static/` не изменялись.
- Следующая логическая задача: `03-frontend-ssr-foundation`.
