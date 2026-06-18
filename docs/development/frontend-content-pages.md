# Статические content pages в Nuxt

## Назначение

В Nuxt добавлены минимальные SSR-страницы для ссылок, которые уже присутствуют в layout и на главной странице:

- `/privacy`
- `/agreement`
- `/book`

Эти страницы нужны, чтобы публичные ссылки не отдавали 404 до появления CMS или полноценных юридических документов.

## Файлы

- `frontend/app/pages/privacy.vue` — политика конфиденциальности.
- `frontend/app/pages/agreement.vue` — пользовательское соглашение.
- `frontend/app/pages/book.vue` — народная жалобная книга.

## Статус текстов

`/privacy` и `/agreement` содержат явную пометку, что это текст-заглушка. Их нельзя считать финальными юридическими документами без отдельного согласования.

`/book` является публичной статической страницей без backend API. Реальный реестр обращений должен подключаться отдельной атомарной задачей.

## SEO

Все три страницы индексируемые и имеют:

- `title`;
- `description`;
- canonical URL;
- SSR HTML с основным контентом.

## Проверки

```bash
docker compose run --rm nuxt sh -lc "corepack enable && corepack prepare pnpm@11.7.0 --activate && pnpm typecheck && pnpm lint && pnpm build"
```

Smoke:

```bash
curl -k https://rukadobra.localhost/privacy
curl -k https://rukadobra.localhost/agreement
curl -k https://rukadobra.localhost/book
```
