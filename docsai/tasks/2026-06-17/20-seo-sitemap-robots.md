# SEO sitemap robots

## Context

`AGENTS.md` требует SSR/SEO-first frontend, sitemap через Nuxt и данные динамических URL от Laravel. Приватные страницы не должны попадать в sitemap и индекс.

## Goal

Реализовать sitemap/robots и backend endpoint динамических sitemap URL.

## Non-goals

- Не реализовывать все страницы заново.
- Не делать отдельный SEO admin.

## Backend changes

- Добавить `GET /api/v1/seo/sitemap-urls`.
- Вернуть опубликованные публичные URL:
  - appeals detail;
  - news detail;
  - categories/static public pages при необходимости.
- Не возвращать private/auth/dashboard/API routes.

## Frontend changes

- Подключить Nuxt sitemap/robots modules.
- Создать server route для sitemap source.
- Настроить robots:
  - disallow `/api`;
  - disallow `/dashboard`;
  - disallow `/login`;
  - disallow `/register`;
  - disallow `/password-reset`;
  - disallow `/verification`.
- Проверить canonical/robots для публичных страниц.

## API contract

### `GET /api/v1/seo/sitemap-urls`

Route name: `api.seo.sitemap-urls`.

Success:

```json
{
  "data": [
    {
      "loc": "https://rukadobra.localhost/appeals/road-pits-after-repair",
      "lastmod": "2026-06-17T12:00:00+03:00"
    }
  ]
}
```

## SEO impact

Ключевая задача для SEO:
- публичные страницы индексируемые;
- приватные страницы noindex/не sitemap;
- missing pages дают 404.

## Tests

- Backend feature test sitemap endpoint.
- Frontend build.
- SEO smoke:
  - sitemap содержит публичные URL;
  - robots закрывает приватные разделы;
  - private routes не попали в sitemap.

## Acceptance criteria

- [x] Sitemap генерируется Nuxt.
- [x] Laravel отдает динамические URL.
- [x] `/api`, `/dashboard`, auth routes не индексируются.
- [x] Публичные detail pages есть в sitemap.
- [x] Postman обновлен.

## Postman

- [x] Добавить `GET /api/v1/seo/sitemap-urls`.
