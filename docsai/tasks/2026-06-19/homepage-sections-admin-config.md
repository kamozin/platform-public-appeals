# Настраиваемые блоки главной страницы

## Context

Главная страница Nuxt уже частично получает данные из `GET /api/v1/home`:

- hero-слайды из `homepage_slides`;
- рекламные баннеры из `advertisements`;
- группы и категории обращений из `category_groups` и `categories`;
- SEO DTO для главной страницы.

Остальные блоки главной пока остаются статическими во frontend-компонентах:

- видео поддержки;
- верхние панели про жалобную книгу и географию работы;
- статистика платформы;
- тексты и настройки блока категорий;
- шаги подачи обращения;
- preview обращений;
- preview новостей;
- блок прозрачности;
- trust-карточки.

Из-за этого администратор не может включать, выключать, сортировать и редактировать все блоки главной страницы без изменения frontend-кода.

## Goal

Сделать главную страницу управляемой из БД и админки без превращения frontend в произвольный page builder.

Нужно:

- определить фиксированный реестр блоков главной страницы;
- добавить хранение настроек блоков: включенность, порядок, заголовки, описания, CTA и typed settings;
- добавить хранение повторяемых элементов блоков: статистика, шаги, trust-карточки, видео-карточки, panel items;
- расширить `GET /api/v1/home`, чтобы все статические данные главной приходили из БД;
- расширить admin API и Nuxt-админку для управления блоками главной;
- оставить новости, обращения, категории, рекламу и hero-слайды в их профильных сущностях, не дублируя их как произвольные карточки главной;
- обновить сидер стартового контента;
- обновить русскую документацию и Postman collection.

## Non-goals

- Не строить свободный drag-and-drop page builder.
- Не давать администратору менять Vue-компоненты, CSS-классы или frontend layout.
- Не трогать исходную папку `static/`.
- Не добавлять Laravel web UI, Blade, Inertia или Livewire.
- Не переносить бизнес-логику во frontend.
- Не дублировать новости и обращения в отдельных таблицах карточек главной.
- Не добавлять загрузку файлов в этой задаче: изображения и видео задаются URL-ами или уже существующими asset-path.
- Не менять публичные URL главной, новостей, обращений и категорий.

## Backend changes

Добавить таблицу `homepage_sections`.

Рекомендуемые поля:

- `id` UUID primary key;
- `code` string unique;
- `title` string nullable;
- `eyebrow` string nullable;
- `description` text nullable;
- `primary_cta_label` string nullable;
- `primary_cta_url` string nullable;
- `secondary_cta_label` string nullable;
- `secondary_cta_url` string nullable;
- `settings` json nullable;
- `sort_order` unsigned integer;
- `is_active` boolean;
- timestamps.

Добавить таблицу `homepage_section_items`.

Рекомендуемые поля:

- `id` UUID primary key;
- `section_code` string indexed;
- `kind` string;
- `title` string nullable;
- `text` text nullable;
- `icon` string nullable;
- `image_url` string nullable;
- `target_url` string nullable;
- `badge` string nullable;
- `meta` json nullable;
- `sort_order` unsigned integer;
- `is_active` boolean;
- timestamps.

Добавить модели:

- `HomepageSection`;
- `HomepageSectionItem`.

Расширить `PublicContentService::home()`:

- возвращать активные секции в порядке `sort_order`;
- возвращать активные элементы секций в порядке `sort_order`;
- брать preview новостей из `news_posts`;
- брать preview обращений из `public_appeals`;
- брать категории из существующих `category_groups` и `categories`;
- брать рекламу из существующей `advertisements`;
- брать hero-слайды из существующей `homepage_slides`.

Расширить `AdminContentService`:

- список секций главной;
- обновление секции;
- список элементов секции;
- создание элемента секции;
- обновление элемента секции;
- удаление элемента секции.

Добавить admin endpoints:

- `GET /api/v1/admin/homepage-sections`
- `PATCH /api/v1/admin/homepage-sections/{id}`
- `GET /api/v1/admin/homepage-section-items`
- `POST /api/v1/admin/homepage-section-items`
- `PATCH /api/v1/admin/homepage-section-items/{id}`
- `DELETE /api/v1/admin/homepage-section-items/{id}`

Имена routes:

- `api.admin.homepage-sections.index`
- `api.admin.homepage-sections.update`
- `api.admin.homepage-section-items.index`
- `api.admin.homepage-section-items.store`
- `api.admin.homepage-section-items.update`
- `api.admin.homepage-section-items.destroy`

## Frontend changes

Расширить типы public home DTO:

- `HomeSectionDto`;
- `HomeSectionItemDto`;
- `HomeContentDto.sections`;
- `HomeContentDto.newsPreview`;
- `HomeContentDto.appealsPreview`.

Обновить `frontend/app/pages/index.vue`:

- получать все данные через `useHomeContent().fetchHome()`;
- передавать в компоненты только данные из DTO;
- рендерить блоки по фиксированному порядку из `sections`;
- не хардкодить тексты блоков в странице.

Обновить компоненты:

- `HomeSupportVideo.vue`;
- `HomeTopPanels.vue`;
- `HomeStatsStrip.vue`;
- `HomeCategoriesPreview.vue`;
- `HomeHowItWorks.vue`;
- `HomeAppealsPreview.vue`;
- `HomeLatestNews.vue`;
- `HomeTransparencyTrust.vue`;
- при необходимости `HomeHero.vue` для телефона и подписи из настроек секции.

Расширить `/admin`:

- добавить вкладку или раздел `Главная`;
- показать список блоков с включением и сортировкой;
- дать редактирование заголовков, описаний, CTA и settings;
- дать CRUD повторяемых элементов для блоков `stats`, `how_it_works`, `support_video`, `top_panels`, `transparency`, `trust_grid`;
- сохранить отдельные существующие вкладки для hero-слайдов и рекламы.

## API contract

`GET /api/v1/home` должен вернуть единый DTO:

```json
{
  "data": {
    "sections": [
      {
        "code": "stats",
        "title": "Показатели платформы",
        "eyebrow": "Обращения получают ход",
        "description": "Описание блока",
        "primaryCtaLabel": "Подать обращение",
        "primaryCtaUrl": "/appeal/new",
        "secondaryCtaLabel": null,
        "secondaryCtaUrl": null,
        "settings": {},
        "items": [
          {
            "kind": "stat",
            "title": "обращений подано",
            "text": "по ЖКХ, дорогам и правам граждан",
            "icon": "file",
            "imageUrl": null,
            "targetUrl": null,
            "badge": "126 540+",
            "meta": {
              "color": "blue"
            }
          }
        ]
      }
    ],
    "slides": [],
    "advertisements": [],
    "categoryGroups": [],
    "appealsPreview": [],
    "newsPreview": [],
    "seo": {}
  }
}
```

Реестр кодов секций:

| Code | Назначение | Данные |
| --- | --- | --- |
| `hero` | Первый экран, телефон, общие CTA | `homepage_sections` + `homepage_slides` |
| `support_video` | Видео поддержки и форма отправки видео | section + items |
| `top_panels` | Жалобная книга и география работы | section + items |
| `home_promo` | Рекламный блок | `advertisements` + section settings |
| `stats` | Показатели платформы | section + items |
| `categories_router` | Маршрутизатор категорий обращения | `categories` + section settings |
| `how_it_works` | Шаги подачи обращения | section + items |
| `appeals_preview` | Лента обращений на главной | `public_appeals` + section settings |
| `latest_news` | Последние новости | `news_posts` + section settings |
| `transparency` | Прозрачность и публичность | section + items |
| `trust_grid` | Карточки доверия | section + items |

Для `appeals_preview` настройки секции должны поддерживать:

- `limit`;
- `statuses`;
- `cities`;
- `sort`.

Для `latest_news` настройки секции должны поддерживать:

- `limit`;
- `featuredSlug` nullable;
- `category` nullable.

Для `categories_router` настройки секции должны поддерживать:

- `primaryLimit`;
- `maxItems`;
- `groupSlugs` nullable.

## SEO impact

Главная страница остается SSR-страницей.

Все тексты, которые важны для SEO и первого HTML, должны приходить через `useAsyncData` на сервере:

- hero title/lead;
- заголовки секций;
- описания секций;
- категории;
- preview новостей;
- preview публичных обращений;
- trust/transparency тексты.

SEO DTO главной должен остаться частью `GET /api/v1/home`.

Для SEO-настроек главной допустимо на первом этапе оставить текущий `seo()` внутри `PublicContentService`, но целевое состояние - хранение title, description, robots и og image в настройках главной страницы или отдельной SEO-сущности.

## Tests

Backend feature tests:

- `GET /api/v1/home` возвращает `sections`;
- выключенная секция не попадает в public DTO;
- порядок секций соответствует `sort_order`;
- выключенный item не попадает в public DTO;
- `appealsPreview` берется только из публичных обращений;
- `newsPreview` берется только из опубликованных новостей;
- admin может обновить секцию;
- admin может создать, обновить и удалить item секции;
- обычный пользователь не имеет доступа к admin endpoints;
- route names для новых admin endpoints.

Frontend checks:

- `pnpm typecheck`;
- `pnpm lint`;
- SSR smoke главной страницы;
- проверить, что в HTML до hydration есть тексты управляемых секций.

## Acceptance criteria

- На главной не остается статических массивов контента для управляемых блоков.
- Все основные блоки главной можно включать и выключать из админки.
- Порядок блоков главной задается из БД.
- Повторяемые элементы блоков управляются из админки.
- Новости на главной берутся из `news_posts`, а не из локального массива.
- Обращения на главной берутся из `public_appeals`, а не из локального массива.
- Категории на главной продолжают браться из `categories`.
- Hero-слайды продолжают управляться через существующий CRUD `homepage-slides`.
- Реклама продолжает управляться через существующий CRUD `advertisements`.
- Nuxt не содержит прямых hardcoded URL вида `/api/v1/...`.
- Исходная папка `static/` не изменена.
- Документация на русском обновлена.
- Postman collection обновлена.
- Backend и frontend проверки проходят.

## Postman

Обновить `docsai/postmancollection/rukadobra-api.postman_collection.json`:

- добавить `GET /home` с новым DTO `sections`;
- добавить `GET /admin/homepage-sections`;
- добавить `PATCH /admin/homepage-sections/{id}`;
- добавить `GET /admin/homepage-section-items`;
- добавить `POST /admin/homepage-section-items`;
- добавить `PATCH /admin/homepage-section-items/{id}`;
- добавить `DELETE /admin/homepage-section-items/{id}`.
