# Backend API foundation

Документ фиксирует базовую настройку Laravel API-only после задачи `02-backend-api-foundation`.

## Входные точки

- Backend работает только как JSON API.
- API routes подключены через `bootstrap/app.php`.
- Laravel автоматически добавляет prefix `/api`, внутри `routes/api.php` используется только version prefix `v1`.
- Текущий health endpoint:
  - Method: `GET`
  - URL: `https://rukadobra.localhost/api/v1/health`
  - Route name: `api.health`

Успешный ответ:

```json
{
  "data": {
    "status": "ok"
  }
}
```

## Ошибки API

Все API-ошибки возвращаются в едином JSON-формате:

```json
{
  "error": {
    "code": "NOT_FOUND",
    "message": "Resource not found.",
    "details": {},
    "trace_id": "trace-id"
  }
}
```

Тексты ошибок лежат в `lang/en/api_errors.php`. В коде нельзя хардкодить человекочитаемый `message`: бизнес-код ошибки должен резолвиться через translations.

Если клиент передает `X-Request-Id`, значение возвращается как `trace_id` и response header `X-Request-Id`. Если header не передан, backend генерирует UUID.

## Локальная проверка

```bash
docker compose up -d
docker compose exec laravel composer test
docker compose exec laravel composer lint
curl -k https://rukadobra.localhost/api/v1/health
```

Для smoke test error contract:

```bash
curl -k https://rukadobra.localhost/api/v1/missing -H 'X-Request-Id: trace-smoke'
```

## Tooling

- Форматирование: Laravel Pint.
- Тесты: Pest.
- Статический анализ: PHPStan + Larastan.

Composer scripts:

```bash
composer test
composer lint
composer lint:fix
composer analyse
```
