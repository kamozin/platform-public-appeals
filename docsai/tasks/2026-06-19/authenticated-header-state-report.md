# Отчёт: состояние шапки для авторизованного пользователя

## Что сделано

Шапка теперь учитывает состояние авторизации:

- при отсутствии token показывает `Войти`;
- при наличии token показывает ссылку `Кабинет`;
- после загрузки `auth/me` для admin-пользователя показывает `Админка`;
- показывает кнопку `Выйти`;
- мобильное меню использует тот же набор действий;
- при наличии token шапка сама вызывает `fetchMe()`, если пользователь ещё не загружен в state.

## Затронутые файлы

- `frontend/app/components/layout/AppHeader.vue`
- `frontend/app/assets/styles/main.css`

## Backend

Backend не менялся.

## Postman

Не требуется.

## Проверки

Выполнено:

```bash
docker compose exec -T nuxt pnpm typecheck
docker compose exec -T nuxt pnpm lint
```

Результат: успешно.

Runtime smoke:

- `GET https://rukadobra.localhost/dashboard` через nginx возвращает `200 OK`;
- `GET https://rukadobra.localhost/admin` через nginx возвращает `200 OK`;
- для `/admin` сохранён `X-Robots-Tag: noindex, nofollow`.
