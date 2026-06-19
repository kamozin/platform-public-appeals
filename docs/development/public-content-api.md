# Публичный контент и admin API

Документ описывает API для публичных SSR-страниц Nuxt и базовые endpoints администрирования контента.

## Назначение

Laravel остаётся API-only и отдаёт только JSON. Nuxt получает данные через `useApi()` и рендерит публичные страницы на SSR/SWR.

Публичные endpoint-ы:

- `GET /api/v1/home`
- `GET /api/v1/categories`
- `GET /api/v1/news`
- `GET /api/v1/news/{slug}`
- `GET /api/v1/appeals`
- `GET /api/v1/appeals/{slug}`
- `GET /api/v1/appeals/{appeal}/comments`
- `GET /api/v1/seo/sitemap-urls`

Источник данных: таблицы БД, заполненные сидером `Database\Seeders\PublicContentSeeder` и управляемые через admin API.

## Таблицы контента

Основные таблицы:

- `category_groups`
- `categories`
- `homepage_slides`
- `advertisements`
- `news_posts`
- `public_appeals`
- `public_appeal_attachments`
- `public_appeal_timeline_items`
- `public_appeal_documents`
- `public_appeal_official_responses`
- `appeal_comments`

`appeal_comments` используется и для опубликованных комментариев публичного реестра, и для новых пользовательских комментариев со статусом `pending`.

`homepage_slides` и `advertisements` используются главной страницей. Публичный API отдаёт только активные записи; для рекламы дополнительно учитываются `starts_at` и `ends_at`.

## Главная страница

`GET /api/v1/home` возвращает данные главной страницы для SSR:

- активные hero-слайды;
- активные рекламные баннеры placement `home_promo`;
- активные группы категорий с активными категориями;
- SEO-метаданные главной.

Формат:

```json
{
  "data": {
    "slides": [
      {
        "id": "...",
        "eyebrow": "НКО Рука добра",
        "title": "Подавайте обращения...",
        "description": "...",
        "imageUrl": "/assets/slide.png",
        "primaryCtaLabel": "Подать обращение",
        "primaryCtaUrl": "/appeal/new",
        "secondaryCtaLabel": "Как это работает",
        "secondaryCtaUrl": "/about",
        "sortOrder": 10
      }
    ],
    "advertisements": [
      {
        "id": "...",
        "placement": "home_promo",
        "title": "Помогаем быстрее",
        "description": "...",
        "imageUrl": "/assets/banner.png",
        "targetUrl": "/appeal/new",
        "label": "Партнёрский проект",
        "sortOrder": 10
      }
    ],
    "categoryGroups": [],
    "seo": {}
  }
}
```

## Публичные категории

`GET /api/v1/categories` возвращает активные группы и активные категории из БД. Активная группа без активных категорий не выводится в публичный ответ, чтобы SSR-страница `/categories` не строила пустые секции.

Формат:

```json
{
  "data": {
    "groups": [
      {
        "slug": "city",
        "title": "Дом и город",
        "categories": [
          {
            "id": "...",
            "slug": "roads",
            "title": "Дороги",
            "description": "...",
            "icon": "road"
          }
        ]
      }
    ],
    "seo": {}
  }
}
```

## Новости

Публично отдаются только записи `news_posts.status = published`.

`GET /api/v1/news` поддерживает:

- `page`
- `per_page`

`GET /api/v1/news/{slug}` возвращает DTO новости и SEO-метаданные. Черновики и архивные записи возвращают `NOT_FOUND`.

## Публичные обращения

Публично отдаются только записи `public_appeals.is_public = true`.

`GET /api/v1/appeals` поддерживает:

- `search`
- `status`: `checking`, `active`, `resolved`
- `city`
- `category`
- `sort`: `newest`, `popular`, `resolved`
- `page`
- `per_page`

Отфильтрованные страницы получают `seo.robots = noindex,follow`.

`GET /api/v1/appeals/{slug}` возвращает карточку обращения, вложения, таймлайн, документы, официальный ответ, preview комментариев и SEO.

## Admin access

Admin endpoints требуют bearer token пользователя с `users.is_admin = true`.

Ошибки:

- без токена: `UNAUTHORIZED`, HTTP 401;
- пользователь без admin-доступа: `FORBIDDEN`, HTTP 403;
- отсутствующая сущность: `NOT_FOUND`, HTTP 404;
- ошибка валидации: `VALIDATION_FAILED`, HTTP 422.

## Admin appeal moderation

Модерация пользовательских обращений отделена от CRUD публичных карточек.
`/api/v1/admin/appeal-moderation` работает с отправленными `appeal_drafts`, а `/api/v1/admin/appeals` продолжает управлять уже созданными `public_appeals`.

Endpoints:

- `GET /api/v1/admin/appeal-moderation`
- `GET /api/v1/admin/appeal-moderation/{id}`
- `POST /api/v1/admin/appeal-moderation/{id}/request-changes`
- `POST /api/v1/admin/appeal-moderation/{id}/reject`
- `POST /api/v1/admin/appeal-moderation/{id}/approve`

`GET /api/v1/admin/appeal-moderation` поддерживает:

- `status`: `pending_moderation`, `needs_changes`, `rejected`, `approved`, `all`;
- `page`;
- `per_page`.

Detail-ответ показывает исходные данные обращения, контакты заявителя, metadata вложений и `events[]`.
Контакты доступны только admin API и не копируются в публичную карточку автоматически.

Переходы:

- `request-changes` переводит draft в `needs_changes` и сохраняет `moderationNote`;
- пользователь может повторно отправить `needs_changes`, после чего draft возвращается в `pending_moderation`;
- `reject` переводит draft в `rejected` и сохраняет `rejectionReason`;
- `approve` в транзакции создаёт запись в `public_appeals`, сохраняет `publicAppealId` и переводит draft в `approved`.

Финальные статусы `rejected` и `approved` не принимают повторные действия модерации и возвращают `CONFLICT`.

## Admin categories

- `GET /api/v1/admin/categories`
- `POST /api/v1/admin/categories`
- `PATCH /api/v1/admin/categories/{id}`
- `DELETE /api/v1/admin/categories/{id}`

Минимальный payload создания:

```json
{
  "group_slug": "city",
  "group_title": "Дом и город",
  "slug": "roads",
  "title": "Дороги",
  "description": "Ямы, тротуары, освещение",
  "icon": "road",
  "is_active": true
}
```

## Admin homepage slides

- `GET /api/v1/admin/homepage-slides`
- `POST /api/v1/admin/homepage-slides`
- `PATCH /api/v1/admin/homepage-slides/{id}`
- `DELETE /api/v1/admin/homepage-slides/{id}`

Минимальный payload создания:

```json
{
  "eyebrow": "НКО Рука добра",
  "title": "Подавайте обращения в один понятный маршрут",
  "description": "Опишите проблему, приложите материалы и отслеживайте ответ.",
  "image_url": "/assets/hero-hands-heart.png",
  "primary_cta_label": "Подать обращение",
  "primary_cta_url": "/appeal/new",
  "secondary_cta_label": "Как это работает",
  "secondary_cta_url": "/about",
  "is_active": true,
  "sort_order": 10
}
```

## Admin advertisements

- `GET /api/v1/admin/advertisements`
- `POST /api/v1/admin/advertisements`
- `PATCH /api/v1/admin/advertisements/{id}`
- `DELETE /api/v1/admin/advertisements/{id}`

Минимальный payload создания:

```json
{
  "placement": "home_promo",
  "title": "Получите помощь с обращением",
  "description": "Команда поддержки подскажет, как оформить документы.",
  "image_url": "/assets/support-banner.png",
  "target_url": "/appeal/new",
  "label": "Поддержка",
  "alt": "Помощь с обращением",
  "is_active": true,
  "sort_order": 10
}
```

Для ограничения периода показа можно передать `starts_at` и `ends_at`.

## Admin news

- `GET /api/v1/admin/news`
- `POST /api/v1/admin/news`
- `PATCH /api/v1/admin/news/{id}`
- `DELETE /api/v1/admin/news/{id}`

Минимальный payload публикации:

```json
{
  "slug": "admin-published-news",
  "title": "Новость из админки",
  "excerpt": "Короткое описание",
  "content": "Полный текст",
  "category": "Новости проекта",
  "image_url": "/assets/hero-hands-heart.png",
  "status": "published"
}
```

Если `status = published` и `published_at` не передан, backend выставляет текущую дату.

## Admin appeals

- `GET /api/v1/admin/appeals`
- `POST /api/v1/admin/appeals`
- `PATCH /api/v1/admin/appeals/{id}`
- `DELETE /api/v1/admin/appeals/{id}`

Payload может включать базовые поля, вложения, таймлайн, документы и официальный ответ.

```json
{
  "slug": "admin-public-appeal",
  "title": "Публичное обращение из админки",
  "excerpt": "Короткое описание",
  "description": "Подробное описание",
  "status": "checking",
  "status_label": "На проверке",
  "city": "Казань",
  "district": "Центральный район",
  "category": "Дороги",
  "location": "Казань",
  "image_url": "/assets/issue-road.png",
  "is_public": true,
  "attachments": [
    {
      "type": "image",
      "url": "/assets/issue-road.png",
      "title": "Фото проблемы"
    }
  ],
  "timeline": [
    {
      "status": "published",
      "title": "Опубликовано",
      "happened_at": "2026-06-19T10:00:00+03:00",
      "text": "Обращение опубликовано."
    }
  ],
  "documents": [
    {
      "title": "Документ",
      "url": "#"
    }
  ],
  "official_response": {
    "title": "Ответ ожидается",
    "text": "Запрос направлен."
  }
}
```

Если `is_public = true` и `published_at` не передан, backend выставляет текущую дату.

## Nuxt admin UI

Раздел управления контентом находится на странице:

```txt
/admin
```

Страница работает через `frontend/app/composables/useAdminContent.ts`, который использует общий `useApi()` и bearer token из `useAuth()`.
Прямого хардкода `/api/v1` в странице нет.

Доступ:

- при отсутствии токена пользователь отправляется на `/login`;
- если `auth/me` возвращает `isAdmin = false`, CRUD-интерфейс не показывается;
- admin-пользователь может управлять категориями, слайдером главной, рекламой, новостями и обращениями через admin endpoints.

Файлы frontend:

- `frontend/app/pages/admin/index.vue`
- `frontend/app/composables/useAdminContent.ts`
- `frontend/app/composables/useHomeContent.ts`
- `frontend/app/types/api/admin-content.ts`
- `frontend/app/types/api/public-content.ts`
- `frontend/app/types/api/private.ts`
- `frontend/app/assets/styles/main.css`
- `frontend/nuxt.config.ts`

SEO:

- `/admin` и `/admin/**` работают как client-only страницы;
- для них выставляется `X-Robots-Tag: noindex, nofollow`;
- страница дополнительно вызывает `useNoindexSeo()`.

Postman collection обновлена: добавлены `GET /home`, admin CRUD для `homepage-slides` и admin CRUD для `advertisements`.

## SEO и sitemap

`GET /api/v1/seo/sitemap-urls` строит URL из:

- статичных публичных страниц;
- опубликованных новостей;
- публичных обращений.

`lastmod` берётся из `updated_at` соответствующей записи.

## Проверки

Контракт покрыт feature-тестами:

- `tests/Feature/Api/CategoriesEndpointTest.php`
- `tests/Feature/Api/NewsEndpointTest.php`
- `tests/Feature/Api/AppealsEndpointTest.php`
- `tests/Feature/Api/SeoEndpointTest.php`
- `tests/Feature/Api/HomeContentEndpointTest.php`
- `tests/Feature/Api/AdminContentEndpointTest.php`

Проверки запускаются внутри контейнера `laravel`:

```bash
docker compose exec -T laravel composer test
docker compose exec -T laravel composer lint
```
