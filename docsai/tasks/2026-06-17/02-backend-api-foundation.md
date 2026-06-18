# Backend API foundation

## Context

После Docker foundation нужен минимальный Laravel 13 API-only backend, который соответствует `AGENTS.md`: только JSON, именованные API routes, единый формат ошибок, FormRequest/DTO/use-case как стандарт для будущих endpoint-ов.

## Goal

Создать базовый Laravel API foundation и минимальный health endpoint для проверки инфраструктуры.

## Non-goals

- Не реализовывать auth.
- Не создавать доменные сущности обращений.
- Не добавлять Blade, Inertia, Livewire или Laravel web UI.

## Backend changes

- Установить/инициализировать Laravel 13 API-only.
- Настроить `routes/api.php` с prefix `v1`.
- Добавить `GET /api/v1/health`.
- Настроить единый JSON error contract.
- Добавить базовые translation messages для API errors.
- Подключить Pint, Pest, PHPStan/Larastan.

## Frontend changes

Нет.

## API contract

### `GET /api/v1/health`

Route name: `api.health`.

Success `200`:

```json
{
  "data": {
    "status": "ok"
  }
}
```

Error format for future API:

```json
{
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "Internal error.",
    "details": {},
    "trace_id": "..."
  }
}
```

## SEO impact

Нет.

## Tests

- Feature test на `GET /api/v1/health`.
- Тест базового JSON error contract, если подключается централизованный обработчик.

## Acceptance criteria

- [ ] Laravel отвечает только JSON.
- [ ] В `routes/api.php` нет ручного `prefix('api')`.
- [ ] Health route именован.
- [ ] `trace_id` возвращается в ошибках.
- [ ] `composer test` проходит.
- [ ] `composer lint` или доступный аналог проходит.

## Postman

- [ ] Создать коллекцию, если ее еще нет.
- [ ] Добавить `GET /api/v1/health`.

