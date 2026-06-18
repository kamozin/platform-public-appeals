# AGENTS.md

## Назначение документа

Этот файл задаёт правила для AI-агентов и разработчиков, работающих с проектом.

Цель: делать изменения предсказуемо, атомарно, типизированно, тестируемо и без архитектурного расползания.

Проект: **Жалобная книга / обращения граждан**.

Ключевая архитектура:

- Backend: **Laravel 13 API-only**.
- Frontend: **Nuxt 4 / Vue 3 / TypeScript / SSR**.
- Репозиторий: **один проект / monorepo**.
- Laravel отдаёт только JSON API.
- Nuxt отвечает за SSR, SEO, публичные страницы, sitemap, robots, meta, Open Graph.
- `static/` содержит исходную вёрстку. Её не правим, а используем как источник для переноса во frontend.

---

## Главные запреты

Нельзя:

- добавлять Blade UI;
- добавлять Laravel web UI;
- добавлять Inertia / Livewire без отдельного архитектурного решения;
- превращать Laravel в рендерер HTML;
- делать публичный SEO-фронт как обычную SPA;
- править исходную папку `static/`;
- писать бизнес-логику в контроллерах;
- таскать массивы через Application / Domain вместо DTO / VO;
- использовать Eloquent / Facades / Request / DB / Log / Cache внутри Domain;
- хардкодить URL вида `/api/v1/...` в приложении;
- делать большие “комбайн”-изменения без атомарной спеки;
- начинать писать реальный код до явной команды пользователя.

---

## Целевая структура проекта

```txt
project/
├── app/                         # Laravel backend
│   ├── Domain/
│   ├── Application/
│   ├── Infrastructure/
│   └── Http/
├── bootstrap/
│   ├── app.php
│   └── providers.php
├── config/
├── database/
├── docs/
├── docsai/
│   ├── ai-agent-task-workflow.md
│   └── tasks/
├── frontend/                    # Nuxt 4 / Vue 3 / TypeScript / SSR
│   ├── app/
│   ├── assets/
│   ├── components/
│   ├── composables/
│   ├── layouts/
│   ├── pages/
│   ├── public/
│   ├── server/
│   ├── types/
│   ├── nuxt.config.ts
│   ├── package.json
│   └── tsconfig.json
├── routes/
│   ├── api.php
│   └── console.php
├── static/                      # исходная статическая вёрстка, read-only
├── tests/
├── docker-compose.yml
├── composer.json
└── AGENTS.md
```

---

# 1. Общий процесс работы AI-агента

## 1.1. Сначала обсуждение, потом код

По умолчанию агент **не пишет реальный код сразу**.

Порядок работы:

1. Обсудить задачу в чате.
2. Уточнить бизнес-цель и ограничения.
3. Предложить архитектурный вариант.
4. Декомпозировать на атомарные задачи.
5. Для каждой задачи подготовить отдельную markdown-спеку.
6. Дождаться явной команды на выполнение.
7. Только после этого менять файлы проекта.

Исключение: пользователь прямо просит сразу создать/изменить конкретный файл.

---

## 1.2. Атомарные спеки

Каждая задача AI-агента оформляется отдельным markdown-файлом:

```txt
docsai/tasks/YYYY-MM-DD/<short-slug>.md
```

Пример:

```txt
docsai/tasks/2026-06-17/add-appeals-public-page.md
```

Одна спека = одна атомарная задача = один PR или один маленький набор изменений.

Спека должна содержать:

```md
# <Task title>

## Context

Что есть сейчас и почему задача нужна.

## Goal

Что должно измениться после выполнения.

## Non-goals

Что в этой задаче не делаем.

## Backend changes

Что меняется в Laravel API.

## Frontend changes

Что меняется в Nuxt.

## API contract

Новые/изменённые endpoints, DTO, ошибки.

## SEO impact

Meta, canonical, robots, sitemap, SSR/prerender/SWR.

## Tests

Какие тесты добавить/обновить.

## Acceptance criteria

Чёткий чеклист готовности.

## Postman

Нужно ли создать или обновить коллекцию.
```

---

## 1.3. TDD и порядок реализации

Предпочтительный порядок:

1. Спека.
2. Тесты на Domain / Application.
3. Минимальная реализация.
4. Feature tests API.
5. Frontend SSR/SEO реализация.
6. Frontend tests, если применимо.
7. Static analysis / lint / formatting.
8. Документация на русском.
9. Postman collection: создать или дополнить.

---

## 1.4. После выполнения задачи

После каждой выполненной задачи нужно:

- обновить или создать markdown-документацию на русском;
- описать, что было сделано;
- указать затронутые файлы;
- указать тесты/проверки;
- создать или дополнить Postman collection для API endpoint-ов;
- не смешивать следующую задачу в тот же PR.

---

## 1.5. Язык общения и документации

- Ответы пользователю: **на русском**.
- Документация проекта в `docs/` и `docsai/`: **на русском**.
- Комментарии в коде и PHPDoc / TSDoc: предпочтительно **на английском**.
- Машинные коды ошибок: **English UPPER_SNAKE_CASE**.

---

# 2. Архитектура проекта

## 2.1. Runtime-схема

```txt
Browser
  ↓
Nginx / reverse proxy
  ├── /api/*  → Laravel PHP-FPM
  └── /*      → Nuxt SSR Node server
```

Снаружи это один сайт:

```txt
https://example.com/             → Nuxt SSR
https://example.com/about        → Nuxt SSR
https://example.com/appeals/...  → Nuxt SSR
https://example.com/api/v1/...   → Laravel JSON API
```

---

## 2.2. Backend / Frontend ответственность

Laravel отвечает за:

- API;
- авторизацию;
- бизнес-логику;
- use-cases;
- транзакции;
- хранение данных;
- интеграции;
- DTO для страниц;
- SEO-данные как часть API-контракта;
- данные для sitemap.

Nuxt отвечает за:

- SSR;
- публичный frontend;
- SEO-rendering;
- meta tags;
- canonical;
- Open Graph / Twitter cards;
- robots rules;
- sitemap generation;
- structured data;
- frontend routing;
- UI-компоненты;
- перенос статической вёрстки из `static/`.

Laravel **не рендерит HTML**.

Nuxt **не содержит бизнес-логику backend-а**.

---

## 2.3. Static-вёрстка

Папка `static/` — источник исходной вёрстки.

Правила:

- `static/` не редактируем;
- переносим HTML/CSS/asset-структуру в Nuxt постепенно;
- при переносе сохраняем смысловую структуру, классы и визуальное поведение;
- если нужно изменить вёрстку — меняем уже Nuxt-компонент, а не исходник в `static/`.

Пример переноса:

```txt
static/index.html
  ↓
frontend/pages/index.vue

static/appeal.html
  ↓
frontend/pages/appeals/[slug].vue

static/partials/header.html
  ↓
frontend/components/layout/AppHeader.vue
```

---

# 3. Backend: Laravel API-only

## 3.1. База backend-а

- Laravel: `13.x`.
- PHP: `8.2–8.5` согласно принятой политике проекта.
- Формат: **API-only**.
- Ответы: **только JSON**.
- Blade / web UI: **запрещены**.

---

## 3.2. Laravel 13 bootstrap-настройки

Laravel 13 опирается на настройки в `bootstrap/*`.

Где что настраивать:

- Routing / prefixes: `bootstrap/app.php` через `withRouting(...)`.
- Middleware: `bootstrap/app.php` через `withMiddleware(...)`.
- Exceptions: `bootstrap/app.php` через `withExceptions(...)`.
- Service Providers: `bootstrap/providers.php`.
- Scheduler: `routes/console.php`.

---

## 3.3. API routing

Для API-проекта нужно убедиться, что API-роутинг включён:

```bash
php artisan install:api
```

Это создаёт `routes/api.php`, включает `api` middleware group и добавляет префикс `/api` для маршрутов из `routes/api.php`.

Поэтому внутри `routes/api.php` нельзя вручную писать:

```php
Route::prefix('api')
```

Версия API задаётся внутри `routes/api.php`:

```php
Route::prefix('v1')
    ->name('api.')
    ->group(function (): void {
        // routes
    });
```

Итоговый внешний путь:

```txt
/api/v1/...
```

---

## 3.4. Правила роутов

Обязательно:

- каждый публичный API route должен иметь `->name(...)`;
- роуты ведут в контроллеры или invokable actions;
- жирные closures в routing запрещены;
- route params должны иметь ограничения: `whereUuid`, `whereNumber`, etc.;
- URL в коде не хардкодим;
- для генерации ссылок используем `route(...)`.

Имена роутов:

```txt
api.<module>.<resource>.<action>
```

Примеры:

```txt
api.auth.login
api.users.index
api.users.show
api.appeals.show
api.webhooks.sumsub.handle
```

Если активна только одна версия API, версию в имени route не дублируем.

Если параллельно поддерживаются несколько версий, допустимо:

```txt
api.v1.appeals.show
api.v2.appeals.show
```

---

## 3.5. Пример `routes/api.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.')
    ->group(function (): void {
        Route::prefix('auth')->name('auth.')->group(function (): void {
            Route::post('/login', \App\Http\Controllers\Auth\LoginController::class)
                ->name('login');

            Route::post('/logout', \App\Http\Controllers\Auth\LogoutController::class)
                ->name('logout');
        });

        Route::prefix('appeals')->name('appeals.')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Appeals\AppealController::class, 'index'])
                ->name('index');

            Route::get('/{id}', [\App\Http\Controllers\Appeals\AppealController::class, 'show'])
                ->whereUuid('id')
                ->name('show');

            Route::post('/', [\App\Http\Controllers\Appeals\AppealController::class, 'store'])
                ->name('store');
        });
    });
```

---

# 4. Backend architecture: DDD-lite

## 4.1. Слои

Используем DDD-lite без фанатизма.

```txt
Interface / Http
  Controllers
  FormRequest
  JsonResource
  Routing

Application
  Use-cases
  Commands / Queries
  DTO
  Transactions
  Orchestration
  Result
  Contracts

Domain
  Entities
  Value Objects
  Aggregates
  Domain Services
  Invariants
  Domain Errors

Infrastructure
  Eloquent
  Query Builder
  Repositories
  External API clients
  Queue adapters
  Cache adapters
  Integration adapters
```

---

## 4.2. Правило зависимостей

Domain не зависит от Laravel.

В Domain запрещены:

- `Request`;
- `FormRequest`;
- `JsonResource`;
- Eloquent models;
- Facades;
- `DB`;
- `Log`;
- `Cache`;
- `Http`;
- framework-specific helpers.

Зависимости идут так:

```txt
Http → Application → Domain
Infrastructure → Application/Domain contracts
```

---

## 4.3. Interface / Http слой

Контроллер:

- принимает `FormRequest`;
- получает `$request->validated()`;
- создаёт DTO / Command;
- вызывает один use-case;
- возвращает JSON через `JsonResource` или mapper;
- не содержит бизнес-логику;
- не строит DB-запросы;
- не управляет транзакциями;
- не решает бизнес-ошибки вручную.

---

## 4.4. Application слой

Application отвечает за:

- use-cases;
- транзакции;
- orchestration;
- работу с интерфейсами репозиториев;
- вызовы интеграций через contracts;
- возврат `Result` или DTO;
- маппинг бизнес-результата в application-level result.

Транзакции (`DB::transaction`) живут в Application use-case, не в контроллере.

---

## 4.5. Domain слой

Domain отвечает за:

- инварианты;
- доменные правила;
- Value Objects;
- Entities;
- Aggregates;
- Domain Services;
- доменные ошибки.

Domain должен быть максимально чистым PHP-кодом.

---

## 4.6. Infrastructure слой

Infrastructure отвечает за:

- Eloquent;
- Query Builder;
- реализации репозиториев;
- внешние HTTP API;
- очереди;
- кэш;
- файловое хранилище;
- адаптеры интеграций;
- framework-specific реализацию контрактов.

---

# 5. Backend code style

## 5.1. PSR

Используем:

- PSR-4 для autoloading;
- PSR-12 как базовый coding style;
- PSR-3 для logger contracts;
- PSR-16 только если нужна совместимость с библиотеками.

Не используем напрямую в приложении:

- PSR-7;
- PSR-15.

Их можно использовать внутри Infrastructure-интеграций, если этого требует библиотека.

---

## 5.2. Типизация

В PHP-коде:

- `declare(strict_types=1);` в домене, application, DTO, VO, infrastructure-сервисах;
- типы аргументов и return values обязательны везде, где это не конфликтует с Laravel contracts;
- DTO / VO по умолчанию `readonly`;
- nullable-типы использовать явно;
- mixed избегать;
- array shapes допустимы только на границах и с PHPDoc для static analysis.

---

## 5.3. Управляющие конструкции

Правила:

- вложенность `if`/циклов: максимум 1 уровень;
- 2 уровня допустимы только как исключение;
- сложные ветвления — через `match`;
- `switch` не используем;
- цепочки `if/elseif` избегаем;
- тернарные операторы не используем;
- guard clauses предпочтительнее вложенных условий.

---

## 5.4. Массивы и коллекции

Массивы разрешены на границах:

- `FormRequest::validated()`;
- `config/*`;
- Eloquent internals;
- `JsonResource` output;
- внешние raw payload до маппинга.

Массивы запрещены как основная структура внутри Application / Domain.

Вместо массивов:

- DTO;
- VO;
- typed collections;
- Result objects.

---

## 5.5. Исключения

Не используем исключения как обычную бизнес-ветку.

Допустимые подходы:

1. Исключение всплывает и централизованно маппится в `bootstrap/app.php` через `withExceptions(...)`.
2. Use-case возвращает `Result` для ожидаемых бизнес-результатов.

Не пишем `try/catch` на всякий случай.

---

## 5.6. Комментарии и PHPDoc

Код должен быть самодокументируемым.

Комментарии пишем только когда они объясняют **почему**, а не **что**.

Допустимые форматы:

```php
// Why: ...
// NOTE: ...
// TODO(docsai/tasks/YYYY-MM-DD/<short-slug>.md): ...
```

Запрещено:

```php
// FIXME
```

без ссылки на задачу или спеку.

PHPDoc обязателен, если тип нельзя выразить сигнатурой PHP:

- generics;
- Result types;
- typed collections;
- array shapes на границах;
- `@throws`, если исключение часть публичного контракта;
- публичные Application / Domain contracts.

Пример:

```php
/**
 * @return Result<UserDto, DomainError>
 */
public function handle(CreateUserCommand $command): Result
{
    // ...
}
```

---

# 6. Backend DTO, FormRequest, JsonResource

## 6.1. FormRequest

Валидация входа всегда через `FormRequest`.

Контроллер использует только:

```php
$request->validated()
```

После `validated()` данные сразу маппятся в DTO / Command.

---

## 6.2. DTO

DTO:

- immutable;
- `readonly`;
- strict typed;
- создаётся через named constructor;
- не содержит business logic;
- не является Eloquent model.

Пример:

```php
<?php

declare(strict_types=1);

namespace App\Application\Appeals\Commands;

final readonly class CreateAppealCommand
{
    public function __construct(
        public string $title,
        public string $body,
        public string $authorEmail,
    ) {}

    /**
     * @param array{title: string, body: string, author_email: string} $payload
     */
    public static function fromValidated(array $payload): self
    {
        return new self(
            title: $payload['title'],
            body: $payload['body'],
            authorEmail: $payload['author_email'],
        );
    }
}
```

---

## 6.3. JsonResource

`JsonResource` отвечает только за формат ответа.

Можно:

- переименовывать поля;
- менять вложенность;
- приводить типы;
- собирать вложенные resources.

Нельзя:

- делать DB-запросы;
- вызывать сервисы;
- принимать бизнес-решения;
- обращаться к внешним API;
- выполнять тяжёлые вычисления.

---

# 7. API errors

## 7.1. Формат ошибки

Единый формат:

```json
{
  "error": {
    "code": "USER_NOT_FOUND",
    "message": "User not found.",
    "details": {},
    "trace_id": "..."
  }
}
```

Правила:

- `code` — стабильный машинный идентификатор;
- `code` пишется на английском в `UPPER_SNAKE_CASE`;
- `message` — человекочитаемый текст;
- по умолчанию `message` возвращается на английском;
- `details` всегда JSON object;
- `trace_id` нужен для логов и трассировки;
- клиенты могут показывать `message`, но логику должны строить по `code`.

---

## 7.2. Тексты ошибок

Тексты ошибок не хардкодим в коде.

Храним централизованно:

```txt
lang/en/api_errors.php
lang/ru/api_errors.php
```

Если структура проекта использует `src/`, допустимо:

```txt
src/lang/en/api_errors.php
src/lang/ru/api_errors.php
```

Ключ = error code.

---

## 7.3. Маппинг статусов

Рекомендуемая таблица:

```txt
VALIDATION_FAILED     → 422
UNAUTHORIZED          → 401
FORBIDDEN             → 403
NOT_FOUND             → 404
CONFLICT              → 409
RATE_LIMITED          → 429
INTEGRATION_FAILED    → 502 / 503
INTERNAL_ERROR        → 500
```

Успешные ответы:

```txt
GET             → 200
POST create     → 201
PUT/PATCH       → 200 или 204, выбрать один стиль
DELETE          → 204
```

Контроллер не должен сам угадывать HTTP status для бизнес-ошибки.

---

# 8. Database

## 8.1. Общие правила

- Контроллеры не строят запросы.
- Eloquent / Query Builder живут в Infrastructure.
- Простые read-only query services допустимы, но не в контроллере.
- Транзакции живут в Application use-case.
- Миграции должны явно задавать индексы для частых фильтров, сортировок, foreign keys.

---

## 8.2. Производительность

Обязательно следить за:

- N+1;
- eager loading через `with(...)` / `load(...)`;
- индексами;
- пагинацией больших списков;
- тяжёлыми сортировками;
- запросами внутри циклов.

---

# 9. Авторизация и безопасность

Правила:

- авторизация через Policies / Gates;
- не писать `if ($user->role === ...)` в контроллерах;
- публичные endpoints ограничивать rate limiting;
- секреты не логировать;
- персональные данные не логировать без необходимости;
- токены, ключи, webhook secrets хранить только через config/env;
- `env()` напрямую вне `config/*` запрещён;
- CORS не расширять без причины;
- для same-origin Nuxt + Laravel предпочтительно минимизировать CORS.

---

# 10. Очереди, события, side effects

## 10.1. Jobs / Listeners

Job / Listener должен быть тонким:

```txt
получил данные → вызвал use-case → закончил
```

Нельзя помещать бизнес-логику в Job / Listener.

---

## 10.2. Идемпотентность

Обязательна для:

- webhook-ов;
- критичных POST endpoints;
- jobs, которые могут повторяться;
- внешних интеграций с оплатами, KYC, биржами.

Используем:

- idempotency key;
- event id;
- request id;
- дедупликацию;
- уникальные индексы там, где это уместно.

---

# 11. Интеграции

## 11.1. Где храним

Все внешние интеграции живут в Infrastructure:

```txt
app/Infrastructure/Integrations/
```

Рекомендуемая структура:

```txt
app/Infrastructure/Integrations/
  Exchanges/
    Kraken/
    Binance/
  Kyc/
    Sumsub/
  Payments/
  Notifications/
```

---

## 11.2. Контракты интеграций

Интерфейсы лежат отдельно от реализаций:

```txt
app/Application/Contracts/Integrations/
```

Примеры:

```txt
app/Application/Contracts/Integrations/Exchanges/MarketDataProvider.php
app/Application/Contracts/Integrations/Exchanges/TradingProvider.php
app/Application/Contracts/Integrations/Kyc/IdentityVerificationProvider.php
```

Domain по умолчанию не знает про внешние API.

---

## 11.3. Реализации провайдеров

Пример:

```txt
app/Infrastructure/Integrations/
  Exchanges/
    Kraken/
      Client/
        KrakenHttpClient.php
        KrakenSigner.php
      DTO/
      Mappers/
      KrakenMarketDataAdapter.php
    Binance/
      Client/
      DTO/
      Mappers/
      BinanceMarketDataAdapter.php
  Kyc/
    Sumsub/
      Client/
      DTO/
      Webhooks/
      SumsubIdentityVerificationAdapter.php
```

---

## 11.4. Правила интеграций

- Контроллеры и Domain не знают про конкретного провайдера.
- Use-cases зависят от интерфейсов.
- Реализации подключаются через Service Provider.
- Внешние raw arrays не выходят за пределы adapter/client слоя.
- Адаптер возвращает DTO / VO / Result.
- Ошибки интеграций маппятся в единый тип ошибки.
- Retry / timeout / rate limits живут внутри Infrastructure.
- HTTP factory внедряем через DI, а не используем статический Facade без необходимости.

---

## 11.5. Webhooks

Webhook routes:

```txt
api.webhooks.<provider>.<action>
```

Примеры:

```txt
api.webhooks.sumsub.handle
api.webhooks.binance.handle
```

Webhook controller:

- принимает FormRequest;
- вызывает один use-case;
- не валидирует подпись руками в контроллере;
- не содержит бизнес-логику.

Проверка подписи:

```txt
app/Infrastructure/Integrations/<Provider>/Webhooks/
```

Идемпотентность webhook-а обязательна.

---

# 12. Frontend: Nuxt 4 / Vue 3 / TypeScript / SSR

## 12.1. Главная frontend-архитектура

Frontend находится в:

```txt
frontend/
```

Используем:

- Nuxt 4;
- Vue 3;
- TypeScript;
- SSR;
- hybrid rendering;
- строгую типизацию;
- SEO-first подход для публичных страниц.

Nuxt — отдельный runtime внутри monorepo.

Laravel остаётся API-only.

---

## 12.2. Почему SSR обязателен

Публичные страницы должны отдавать готовый HTML с контентом и meta-тегами.

Для SEO-страниц запрещено:

- грузить основной контент только через `onMounted`;
- делать страницу пустой до выполнения client-side JS;
- оставлять title/description только на клиенте;
- рендерить важный текст после hydration;
- делать SPA-only публичные страницы, которые должны индексироваться.

---

## 12.3. Какие страницы SSR

SSR / prerender / SWR нужны для:

```txt
/                       главная
/about                  информационные страницы
/contacts               контакты
/appeals                список обращений, если публичный
/appeals/:slug          публичная страница обращения
/news                   новости, если появятся
/news/:slug             статья/новость
/pages/:slug            CMS/static pages
```

SPA / client-only допустимо для:

```txt
/admin/**
/profile/**
/dashboard/**
```

Такие страницы должны иметь `noindex`, если они приватные или не нужны в поиске.

---

## 12.4. Nuxt route rules

Пример:

```ts
export default defineNuxtConfig({
  ssr: true,

  routeRules: {
    '/': { prerender: true },
    '/about': { prerender: true },
    '/contacts': { prerender: true },

    '/appeals/**': {
      swr: 300,
    },

    '/news/**': {
      swr: 300,
    },

    '/admin/**': {
      ssr: false,
      robots: false,
    },

    '/profile/**': {
      ssr: false,
      robots: false,
    },
  },
});
```

Правило: если страница должна индексироваться — сначала думаем про SSR/SWR/prerender, а не про client-only.

---

## 12.5. API base URLs

Для SSR важно разделять server-side и browser-side API base URL.

Server-side Nuxt ходит во внутренний Laravel URL:

```txt
http://laravel/api/v1
```

Browser-side запросы идут same-origin:

```txt
/api/v1
```

Пример `nuxt.config.ts`:

```ts
export default defineNuxtConfig({
  ssr: true,

  typescript: {
    strict: true,
  },

  runtimeConfig: {
    apiInternalBase: process.env.NUXT_API_INTERNAL_BASE || 'http://laravel/api/v1',

    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || '/api/v1',
      siteUrl: process.env.NUXT_PUBLIC_SITE_URL || 'https://example.com',
    },
  },
});
```

Нельзя класть секреты и internal-only значения в `runtimeConfig.public`.

---

## 12.6. API composable

Рекомендуемый подход:

```ts
// frontend/composables/useApi.ts

type ApiOptions = {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
  body?: unknown;
  query?: Record<string, string | number | boolean | undefined>;
};

export function useApi<T>(path: string, options: ApiOptions = {}) {
  const config = useRuntimeConfig();

  const baseURL = import.meta.server
    ? config.apiInternalBase
    : config.public.apiBase;

  return useFetch<T>(path, {
    baseURL,
    method: options.method,
    body: options.body,
    query: options.query,
  });
}
```

Запрещено:

```ts
fetch('https://example.com/api/v1/...')
fetch('/api/v1/...') // напрямую по всему проекту
```

Вместо этого использовать общий composable/client.

---

## 12.7. SSR-safe data fetching

Для SEO-страниц данные нужно получать на сервере:

```vue
<script setup lang="ts">
type AppealPageDto = {
  id: string;
  slug: string;
  title: string;
  description: string;
  body: string;
  seo: {
    title: string;
    description: string;
    canonicalUrl: string;
    ogImageUrl: string | null;
    robots: 'index,follow' | 'noindex,nofollow';
    lastModifiedAt: string | null;
  };
};

const route = useRoute();

const { data, error } = await useApi<AppealPageDto>(
  `/appeals/${route.params.slug}`,
);

if (error.value) {
  throw createError({
    statusCode: error.value.statusCode || 404,
    statusMessage: 'Page not found',
  });
}

useSeoMeta({
  title: () => data.value?.seo.title || data.value?.title || 'Appeal',
  description: () => data.value?.seo.description || data.value?.description || '',
  ogTitle: () => data.value?.seo.title || data.value?.title || 'Appeal',
  ogDescription: () => data.value?.seo.description || '',
  ogImage: () => data.value?.seo.ogImageUrl || undefined,
  robots: () => data.value?.seo.robots || 'index,follow',
});

useHead({
  link: [
    {
      rel: 'canonical',
      href: data.value?.seo.canonicalUrl,
    },
  ],
});
</script>

<template>
  <main v-if="data">
    <h1>{{ data.title }}</h1>
    <div v-html="data.body" />
  </main>
</template>
```

Если используется `v-html`, контент должен быть очищен/sanitized на backend-е или в отдельном безопасном слое.

---

## 12.8. Hydration safety

Нельзя обращаться к browser-only API в SSR-контексте без guard-а.

Опасно:

```ts
const width = window.innerWidth;
```

Правильно:

```ts
const width = ref<number | null>(null);

onMounted(() => {
  width.value = window.innerWidth;
});
```

Или:

```ts
if (import.meta.client) {
  // browser-only logic
}
```

Тяжёлые browser-only компоненты оборачивать в `<ClientOnly>` только если они не важны для SEO.

---

# 13. Frontend SEO rules

## 13.1. SEO-first правило

Каждая индексируемая публичная страница должна иметь:

- SSR-rendered content;
- ровно один осмысленный `h1`;
- `title`;
- `description`;
- canonical URL;
- корректный HTTP status;
- Open Graph tags;
- robots directive;
- нормальные semantic HTML landmarks;
- внутренние ссылки через `<NuxtLink>`;
- alt-тексты для значимых изображений.

---

## 13.2. Laravel SEO DTO

Laravel должен отдавать frontend-у SEO-данные как часть page DTO.

Пример:

```json
{
  "id": "uuid",
  "slug": "example-appeal",
  "title": "Appeal title",
  "description": "Short page description",
  "body": "<p>...</p>",
  "seo": {
    "title": "SEO title",
    "description": "SEO description",
    "canonicalUrl": "https://example.com/appeals/example-appeal",
    "ogImageUrl": "https://example.com/storage/og/example.jpg",
    "robots": "index,follow",
    "lastModifiedAt": "2026-06-17T12:00:00+00:00"
  }
}
```

Правило: Nuxt применяет SEO-данные, но источник истины для динамических страниц — Laravel API.

---

## 13.3. Canonical

Для каждой индексируемой страницы нужен canonical.

Правила:

- canonical должен быть абсолютным URL;
- не плодить дубли со slash/no-slash;
- query params не включать в canonical без необходимости;
- пагинация должна иметь понятную стратегию;
- фильтры и сортировки по умолчанию не индексировать, если нет отдельной SEO-стратегии.

---

## 13.4. HTTP status для SEO

Nuxt должен отдавать реальные статусы:

```txt
существующая страница       → 200
не найдено                 → 404
удалено навсегда            → 410, если применимо
редирект                   → 301 / 302
закрытая страница           → 401 / 403 или noindex, зависит от кейса
ошибка backend-интеграции   → 502 / 503, если страница не может быть собрана
```

Нельзя отдавать `200` для страницы “not found”.

---

## 13.5. Sitemap

Sitemap генерирует Nuxt.

Данные для динамических URL отдаёт Laravel.

Backend endpoint:

```txt
GET /api/v1/seo/sitemap-urls
```

Nuxt server route:

```ts
// frontend/server/api/__sitemap__/urls.ts

type SitemapUrlDto = {
  loc: string;
  lastmod: string | null;
};

export default defineEventHandler(async () => {
  const config = useRuntimeConfig();

  return await $fetch<SitemapUrlDto[]>('/seo/sitemap-urls', {
    baseURL: config.apiInternalBase,
  });
});
```

Nuxt config:

```ts
export default defineNuxtConfig({
  modules: [
    '@nuxtjs/sitemap',
    '@nuxtjs/robots',
  ],

  site: {
    url: process.env.NUXT_PUBLIC_SITE_URL || 'https://example.com',
    name: 'Жалобная книга',
  },

  sitemap: {
    sources: [
      '/api/__sitemap__/urls',
    ],
  },

  robots: {
    disallow: [
      '/admin',
      '/profile',
      '/dashboard',
    ],
  },
});
```

---

## 13.6. Robots

Закрываем от индексации:

```txt
/admin
/profile
/dashboard
/auth
/api
```

Приватные страницы не должны случайно попадать в sitemap.

---

## 13.7. Structured data

Для страниц, где это имеет смысл, добавляем JSON-LD:

- Organization;
- WebSite;
- BreadcrumbList;
- Article / NewsArticle, если появятся новости;
- FAQPage, если есть FAQ;
- ContactPage, если есть контакты.

Structured data должен соответствовать видимому содержимому страницы.

Нельзя добавлять schema.org-разметку “на удачу”.

---

## 13.8. Internal linking

Для SEO важны нормальные ссылки.

Использовать:

```vue
<NuxtLink to="/appeals/example-slug">...</NuxtLink>
```

Не использовать кнопки для навигации, если это обычная ссылка.

---

## 13.9. Images

Правила:

- значимые изображения имеют `alt`;
- декоративные изображения имеют пустой `alt=""`;
- OG image должен быть абсолютным URL;
- не грузить огромные изображения без оптимизации;
- lazy loading не применять к критическому LCP-изображению без причины.

---

# 14. Frontend code style

## 14.1. TypeScript

Правила:

- `strict: true`;
- не использовать `any` без причины;
- API DTO типизировать;
- shared API types хранить в `frontend/types/api/`;
- для UI props использовать явные интерфейсы/types;
- не смешивать API DTO и view model, если формат отличается.

---

## 14.2. Vue components

Правила:

- использовать Composition API;
- использовать `<script setup lang="ts">`;
- компоненты именовать в PascalCase;
- composables именовать `useSomething`;
- не делать огромные компоненты;
- выносить повторяющуюся логику в composables;
- Pinia/store использовать только когда состояние реально глобальное.

---

## 14.3. Suggested frontend structure

```txt
frontend/
  assets/
    styles/
    images/
  components/
    layout/
    ui/
    seo/
    appeals/
  composables/
    useApi.ts
    useCanonical.ts
  layouts/
    default.vue
    admin.vue
  pages/
    index.vue
    about.vue
    contacts.vue
    appeals/
      index.vue
      [slug].vue
  server/
    api/
      __sitemap__/
        urls.ts
  types/
    api/
      appeals.ts
      seo.ts
```

---

# 15. API contract between Nuxt and Laravel

## 15.1. Общие правила

- Laravel отдаёт стабильный JSON contract.
- Nuxt не должен угадывать бизнес-правила.
- DTO должны быть предсказуемыми.
- Ошибки должны приходить в едином формате.
- Для SEO-страниц API должен отдавать всё, что нужно для SSR.

---

## 15.2. Page DTO минимум

Для публичной страницы API должен вернуть:

```txt
id
slug
title
description
body/content
seo.title
seo.description
seo.canonicalUrl
seo.ogImageUrl
seo.robots
seo.lastModifiedAt
```

Если данных недостаточно для полноценного SSR, задачу нельзя считать завершённой.

---

## 15.3. Ошибки для Nuxt SSR

Laravel должен различать:

```txt
NOT_FOUND             → Nuxt отдаёт 404
FORBIDDEN             → Nuxt отдаёт 403 или noindex page
INTEGRATION_FAILED    → Nuxt может отдать 502/503
VALIDATION_FAILED     → обычно 422 в API forms
```

Nuxt не должен превращать всё в `200`.

---

# 16. Tooling

## 16.1. Backend tools

Обязательный набор:

- Laravel Pint;
- Pest;
- PHPStan + Larastan.

Psalm опционально, если нужна дополнительная строгость.

---

## 16.2. Backend checks

Перед завершением backend-задачи выполнить доступные команды проекта.

Предпочтительно:

```bash
vendor/bin/pint
vendor/bin/phpstan analyse
php artisan test
```

Если в `composer.json` есть scripts, использовать проектные scripts:

```bash
composer lint
composer analyse
composer test
```

Правило: **0 новых ошибок static analysis**.

Baseline допустим только как временная мера и не должен прятать новые проблемы.

---

## 16.3. Frontend tools

Рекомендуемый набор:

- ESLint;
- TypeScript / vue-tsc;
- Vitest;
- Playwright для e2e/SEO-smoke, если добавлен;
- package manager по lockfile проекта.

Не менять package manager без отдельного решения.

Если есть `pnpm-lock.yaml`, использовать `pnpm`.
Если есть `package-lock.json`, использовать `npm`.
Если есть `yarn.lock`, использовать `yarn`.

---

## 16.4. Frontend checks

Перед завершением frontend-задачи выполнить доступные команды:

```bash
cd frontend
pnpm lint
pnpm typecheck
pnpm test
pnpm build
```

Если scripts называются иначе, использовать scripts из `frontend/package.json`.

Для SSR/SEO-задач обязательно проверить, что build проходит.

---

# 17. Testing strategy

## 17.1. Backend tests

Тестируем:

- Domain invariants;
- Value Objects;
- Domain Services;
- Application use-cases;
- Result branches;
- error mapping;
- минимальные endpoint feature tests.

Не тестируем:

- каждое поле `JsonResource` ради косметики;
- Laravel internals;
- внешние HTTP API напрямую;
- огромные JSON snapshots без причины.

---

## 17.2. Integration tests

Для интеграций:

- Domain/Application тестируем через fake реализации интерфейсов;
- реальные HTTP вызовы в unit tests запрещены;
- мапперы и DTO можно тестировать отдельно;
- webhook signature validation тестировать на известных payload-примерах.

Fake реализации:

```txt
tests/Fakes/Integrations/
```

---

## 17.3. Frontend tests

Для Nuxt:

- тестировать composables;
- тестировать критичные UI-компоненты;
- проверять SSR-safe rendering для SEO-страниц;
- e2e/smoke для title/meta/canonical/404, если Playwright доступен.

SEO acceptance минимум:

```txt
страница отдаёт 200
HTML содержит основной контент
HTML содержит h1
HTML содержит title
HTML содержит meta description
HTML содержит canonical
404 отдаёт 404
приватные страницы noindex / не в sitemap
```

---

# 18. Deployment / Docker / Reverse proxy

## 18.1. Services

Типовая схема:

```yaml
services:
  nginx:
    image: nginx:alpine
    depends_on:
      - laravel
      - nuxt

  laravel:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    environment:
      APP_ENV: production

  nuxt:
    build:
      context: ./frontend
      dockerfile: Dockerfile
    environment:
      NUXT_API_INTERNAL_BASE: http://laravel/api/v1
      NUXT_PUBLIC_API_BASE: /api/v1
      NUXT_PUBLIC_SITE_URL: https://example.com
```

---

## 18.2. Nginx routing concept

```nginx
location /api/ {
    try_files $uri /index.php$is_args$args;
}

location / {
    proxy_pass http://nuxt:3000;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

Не хардкодить production domain в коде. Использовать env/config.

---

# 19. Postman / API documentation

После каждой API-задачи нужно:

- создать Postman collection, если её ещё нет;
- или дополнить существующую;
- добавить новые endpoints;
- добавить примеры успешных ответов;
- добавить примеры ошибок;
- проверить актуальность base URL / variables.

Если endpoint влияет на Nuxt SSR/SEO, в документации указать:

- какие frontend routes его используют;
- какие SEO-поля он отдаёт;
- какие ошибки важны для HTTP status в SSR.

---

# 20. Конфигурация

Правила:

- значения берём из `env`, но читаем через `config/*`;
- прямой `env()` вне config запрещён;
- secrets не попадать во frontend public runtime config;
- Nuxt public config содержит только безопасные публичные значения;
- internal API URL хранить только в private runtime config.

Laravel config examples:

```txt
config/integrations.php
config/seo.php
config/api.php
```

Nuxt env examples:

```txt
NUXT_API_INTERNAL_BASE=http://laravel/api/v1
NUXT_PUBLIC_API_BASE=/api/v1
NUXT_PUBLIC_SITE_URL=https://example.com
```

---

# 21. Acceptance checklist for every task

Перед завершением задачи агент должен проверить:

```txt
[ ] Задача соответствует атомарной спеке.
[ ] Не добавлен Blade / Inertia / Laravel web UI.
[ ] Laravel остался API-only.
[ ] Nuxt SSR не сломан.
[ ] Публичные SEO-страницы не стали client-only.
[ ] static/ не изменён.
[ ] Контроллеры тонкие.
[ ] Валидация через FormRequest.
[ ] validated() маппится в DTO / Command.
[ ] Domain не зависит от Laravel.
[ ] Eloquent не протащен в Domain/Application.
[ ] Роуты именованные.
[ ] Нет hardcoded /api/v1 URL в коде.
[ ] Ошибки в едином формате.
[ ] Тексты ошибок не захардкожены.
[ ] Для SEO-страниц есть title/description/canonical/robots.
[ ] 404 реально отдаёт 404.
[ ] Sitemap/robots не включают приватные страницы.
[ ] Добавлены/обновлены тесты.
[ ] Запущены доступные проверки.
[ ] Обновлена документация на русском.
[ ] Создана или обновлена Postman collection для API.
```

---

# 22. Когда есть сомнения

Если агент не уверен:

- не делать архитектурный прыжок молча;
- сначала описать варианты;
- указать trade-offs;
- предложить минимальный безопасный шаг;
- не тянуть новую библиотеку без причины;
- не менять архитектуру ради одной маленькой задачи.

Главный принцип:

```txt
Сначала ясность. Потом атомарность. Потом код.
```
