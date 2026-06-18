# Руководство разработчика: чистый код, стиль и приёмка фич (Laravel 12, API-only)

Проект: B2B обменник (white-label). Laravel-приложение живёт в `src/`.  
Цель документа: сделать код **предсказуемым, типизированным, тестируемым** и не плодить легаси.

---

## TL;DR (если соблюдать только 10 пунктов)
1) **Только JSON API**. Никаких Blade/web UI и “html-ответов”.
2) **Каждый публичный роут именованный** (`->name(...)`). Исключение: системный healthcheck (если используем).
3) **Никаких хардкодных URL** в коде (`/api/v1/...` запрещено). Используем `route('api....')`.
4) **FormRequest обязателен** для любого запроса с входными данными. В контроллере — только `$request->validated()`.
5) **Тонкий контроллер**: 1 endpoint → 1 use-case. Без запросов к БД и бизнес‑логики в контроллере.
6) **Слои**: Http → Application → (Domain) → Infrastructure. Domain/Application не тянут Facade/Request/Eloquent внутрь без причины.
7) **Типизация**: `declare(strict_types=1);`, типы аргументов/возвратов, `readonly` DTO/VO.
8) **Условия**: вложенность `if`/циклов ≤ 1; `match` вместо `if/elseif`; **тернарники не используем**.
9) **Качество перед merge**: `composer lint` + `composer test` (внутри `app` контейнера).
10) **Postman**: после добавления/изменения endpoint — обновляем `docsai/postmancolection/b2b-backoffice.postman_collection.json`.

---

## Структура репозитория
- `src/` — Laravel 12 приложение (единственный источник “продуктового” кода).
- `docsai/tasks/` — закрытые спеки/история задач (по датам: `docsai/tasks/YYYY-MM-DD/`).
- `docsai/postmancolection/` — актуальная Postman‑коллекция.

---

## Архитектура (DDD-lite без религии)

### Слои и ответственность
- **Http (Interface)**: Controllers, FormRequest, JsonResource, routing, middleware.
- **Application**: use-cases (оркестрация, транзакции, вызовы портов), DTO/Result.
- **Domain** (когда появится): VO/Entities/инварианты, без Laravel.
- **Infrastructure**: Eloquent/DB query, внешние API/клиенты, очереди, адаптеры, провайдеры интеграций.

### Правило зависимостей (важно)
- Domain не зависит от Laravel.
- Application не должен “знать” про конкретных провайдеров/клиентов — только интерфейсы (порты).
- Infrastructure реализует интерфейсы и регистрируется через Service Provider.

---

## Код-стайл и типизация (минимальный стандарт)

### Обязательное для нового кода
- `declare(strict_types=1);` во всех PHP-классах проекта (кроме случаев, когда ломает контракт фреймворка).
- Типы аргументов/возвратов везде, где возможно.
- DTO/VO — **immutable** (`readonly`) и создаются через named constructor (`fromArray`/`fromValidated`).
- Laravel Pint — единый автоформаттер; руками “стиль” не обсуждаем.

### Управляющие конструкции
- Вложенность `if/циклов` не глубже 1 уровня (редко 2).
- Сложные ветвления — через `match`.
- `switch` избегаем.
- Тернарные операторы **не используем**.

### Массивы
- Массивы разрешены на границах (FormRequest, config, ресурсы, Eloquent).
- Внутри Application/Domain массивы как “коллекции данных” не таскаем: используем DTO/VO/typed collections.

### Исключения
- Не используем exceptions как “обычную логику ветвления”.
- Если ошибка ожидаемая (валидация, not-found, forbidden) — возвращаем типизированный результат/ошибку.
- Если ошибка не ожидаемая — пусть всплывает и маппится централизованно (или фиксится).

---

## HTTP/API контракт (API-only)

### Формат ответа
- Успех: `{"data": ...}`
- Ошибка:
```json
{
  "error": {
    "code": "SOME_CODE",
    "message": "Человекочитаемое сообщение",
    "details": {},
    "trace_id": "..."
  }
}
```

### `trace_id`
- Если клиент прислал `X-Request-Id` — возвращаем его как `trace_id`.
- Иначе генерируем UUID.

### HTTP статусы (договорённость)
- `GET` → 200
- `POST (create)` → 201
- `PUT/PATCH` → 200 или 204 (выбираем единообразно для модуля)
- `DELETE` → 204 (если фактически “deactivate” — всё равно 204 допустим)
- Ошибки:
  - `VALIDATION_FAILED` → 422
  - `UNAUTHORIZED` → 401
  - `FORBIDDEN` → 403
  - `NOT_FOUND` → 404
  - `CONFLICT` → 409
  - `RATE_LIMITED` → 429
  - `INTEGRATION_FAILED` → 502/503
  - `INTERNAL_ERROR` → 500

### Роутинг и имена маршрутов
- API версия в URL: `/api/v1/...`
- Внутри `src/routes/api.php` **не пишем** `prefix('api')` (Laravel добавляет сам).
- Каждый маршрут именованный.
- Соглашение имён: `api.<area>.<module>.<action>`
  - пример: `api.backoffice.countries.index`
- Ограничения параметров: `->whereUuid('id')`, `->whereNumber(...)`.

---

## Контроллеры и FormRequest (как пишем endpoint)

### Контроллер — “тонкий”
Разрешено:
- принять `FormRequest`
- собрать DTO из `$request->validated()`
- вызвать один use-case
- отдать JSON (через JsonResource/mapper)

Запрещено:
- писать бизнес‑логику
- строить Eloquent/DB запросы
- делать “умные” преобразования данных

### FormRequest — единственное место входной валидации
- Контроллер не читает `$request->all()`/`input()`/`get()` напрямую.
- После валидации массив сразу превращаем в DTO и дальше массивы не таскаем.

---

## База данных и Eloquent

### Общие правила
- UUID как идентификатор API‑сущностей (у нас это уже стандарт в большинстве моделей).
- “Удаление” бизнес‑сущностей делаем через `is_active=false`, если это справочник/админка (пример: jurisdictions, operator companies/domains).
- Индексы обязательны для `unique`/FK и часто используемых фильтров.

### Где допустим Eloquent/Query Builder
- В Infrastructure (репозитории/адаптеры/query services).
- В Application допускается как временный компромисс, но **не в контроллерах** (кроме тривиальных read-only, которые будут вынесены позже).

---

## Безопасность (Backoffice)
- Auth: `auth:sanctum` + middleware “только Employee”.
- White-label: operator company резолвится по `Host` через `ResolveOperatorCompany`.
- В non-prod допускается header override домена (см. `config/operator_context.php`), но **в production запрещено**.
- Не логируем: токены, пароли, 2FA коды, секреты TOTP, персональные данные без необходимости.

### 2FA (сейчас)
- Поддерживаем провайдеры: `email_otp`, `totp`.
- Хранилище challenge — таблица `employee_two_factor_challenges` (TTL/attempts/consumed_at).
- Секреты TOTP храним только зашифрованно.

---

## Тесты и качество (Pest + PHPStan + Pint)

### Что тестируем
- Application/use-case: ветвления результата, транзакции, оркестрация.
- Domain (когда появится): инварианты/VO.
- Feature тесты — минимально: “склейка” endpoint → use-case (статус + базовый контракт `data/error.code`).

### Что не тестируем
- Огромные JSON “простыни” (каждое поле ресурса).
- Реальные внешние HTTP/SMTP/провайдеры — только фейки через интерфейсы.

### Команды (в контейнере `app`)
- Линт/стиль+статанализ: `composer lint`
- Автоформатирование: `composer lint:fix`
- Тесты: `composer test`

---

## Приёмка фичи (Definition of Ready / Done)

### Definition of Ready (до начала разработки)
- [ ] Есть спека в `docsai/tasks/YYYY-MM-DD/<short-slug>.md` (контекст, цель, non-goals).
- [ ] Зафиксированы endpoints + имена роутов + статусы.
- [ ] Зафиксированы коды ошибок (`error.code`) и бизнес‑сообщения.
- [ ] Понятны изменения БД (миграции/сидеры) и план отката.
- [ ] Понятен план тестов (хотя бы минимально).

### Acceptance Criteria (в спеке)
Пишем как проверяемые пункты:
- [ ] что должно работать (happy path)
- [ ] ключевые негативные кейсы (404/403/401/422)
- [ ] требования к слоям (FormRequest/тонкий контроллер/use-case)

### Definition of Done (перед merge)
- [ ] Реализация соответствует спеке (AC закрыты).
- [ ] Добавлены/обновлены тесты (минимально необходимое покрытие).
- [ ] `composer lint` зелёный (Pint + PHPStan без новых ошибок).
- [ ] `composer test` зелёный.
- [ ] Роуты именованы и с `whereUuid/whereNumber` где нужно.
- [ ] Нет хардкодных URL строк в коде.
- [ ] Postman коллекция обновлена (если меняли API).
- [ ] Документация обновлена (README/docsai) если изменился способ запуска/настройки/контракт.

### Review checklist (короткий, но строгий)
- [ ] Контроллер тонкий, валидация только в FormRequest.
- [ ] Application/Domain не зависят от Facade/Request/Eloquent без необходимости.
- [ ] Типизация/strict_types соблюдены, нет лишней вложенности/if-цепочек.
- [ ] Ошибки и статусы соответствуют контракту.
- [ ] Тесты и линтеры зелёные.

---

## Что уже реализовано (ориентир по коду/спекам)
Спеки реализованных фич: `docsai/tasks/2026-01-27/*.md`.

### Backoffice (v1)
- `GET /api/v1/backoffice/ping` → `api.backoffice.ping`
- Auth:
  - `POST /api/v1/backoffice/auth/login` → `api.backoffice.auth.login` (с 2FA step-up при необходимости)
  - `POST /api/v1/backoffice/auth/2fa/verify` → `api.backoffice.auth.2fa.verify`
  - `GET /api/v1/backoffice/auth/me` → `api.backoffice.auth.me`
  - `POST /api/v1/backoffice/auth/logout` → `api.backoffice.auth.logout`
- 2FA management (auth:sanctum + employee + operator membership):
  - Email OTP: `api.backoffice.2fa.email.*`
  - TOTP: `api.backoffice.2fa.totp.*`
- Справочники:
  - Countries (read): `api.backoffice.countries.index/show`
  - Jurisdictions (CRUD + deactivate): `api.backoffice.jurisdictions.*`
- White-label управление:
  - Operator companies (CRUD + deactivate): `api.backoffice.operator-companies.*`
  - Domains (list/create/deactivate): `api.backoffice.operator-companies.domains.*`

Код живёт в:
- Http: `src/app/Http/*`
- Application: `src/app/Application/Backoffice/*`
- Infrastructure: `src/app/Infrastructure/*`
