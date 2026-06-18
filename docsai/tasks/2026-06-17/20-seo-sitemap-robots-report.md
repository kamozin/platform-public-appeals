# 20 SEO Sitemap Robots Report

## Что сделано

- Backend endpoint `GET /api/v1/seo/sitemap-urls` уже отдаёт публичные URL.
- Добавлены Nitro routes `/sitemap.xml` и `/robots.txt`.
- `robots.txt` закрывает API, auth, dashboard и wizard routes.
- Приватные страницы используют `useNoindexSeo()`.

## Проверки

- Feature tests `SeoEndpointTest`.
- Nuxt typecheck/lint/build.
- Smoke для `/sitemap.xml` и `/robots.txt`.

## Postman

Sitemap endpoint есть в коллекции.
