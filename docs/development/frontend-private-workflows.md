# Пользовательские Nuxt workflow

Nuxt получил страницы и формы для пользовательских сценариев. Часть страниц требует авторизацию, а форма `/appeal/new` доступна гостю и авторизованному пользователю.

## Страницы

- `/login` — вход.
- `/register` — регистрация.
- `/verification` — отправка и проверка кода.
- `/password-reset` — восстановление пароля.
- `/appeal/new` — мастер создания черновика обращения, доступен без авторизации.
- `/dashboard` — личный кабинет.

Все страницы используют `useNoindexSeo()` и исключены из `robots.txt`.

## Auth state

`useAuth()` хранит Bearer token в cookie `rd_auth_token` и пользователя в `useState`.

API-запросы идут через общий `useApi()`. Для приватных запросов добавляется header:

```txt
Authorization: Bearer <token>
```

Для гостевого черновика `/appeal/new` сохраняет token из `data.guestToken` в cookie `rd_appeal_draft_token` и передаёт его в дальнейших запросах:

```txt
X-Appeal-Draft-Token: <guest-token>
```

## Комментарии и формы

Страница `/appeals/[slug]` получает комментарии через API и отправляет новый комментарий с pending state.

Footer subscription и блок видео поддержки подключены к API:

- `POST /subscriptions/news`
- `POST /support-videos`

## SEO

`/robots.txt` создаётся Nitro route и закрывает:

- `/api`
- `/dashboard`
- `/login`
- `/register`
- `/password-reset`
- `/verification`
- `/appeal/new`

`/sitemap.xml` создаётся Nitro route и берёт URL из `GET /api/v1/seo/sitemap-urls`.
