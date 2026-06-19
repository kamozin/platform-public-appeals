# Отчет: отдельные URL для разделов ЛК и админки

## Что сделано

- Разделы личного кабинета переведены на отдельные frontend-маршруты:
  - `/dashboard/profile`;
  - `/dashboard/drafts`;
  - `/dashboard/appeals`;
  - `/dashboard/saved`;
  - `/dashboard/comments`;
  - `/dashboard/notifications`;
  - `/dashboard/security`;
  - `/dashboard/achievements`.
- Разделы админки переведены на отдельные frontend-маршруты:
  - `/admin/categories`;
  - `/admin/slides`;
  - `/admin/advertisements`;
  - `/admin/news`;
  - `/admin/appeals`.
- Боковые меню ЛК и админки теперь используют `NuxtLink`, поэтому выбранный раздел сохраняется после перезагрузки страницы.
- Старые входные маршруты `/dashboard` и `/admin` оставлены как клиентские точки входа и переводят пользователя на профиль ЛК или категории админки.
- Удален бейдж `2 подтверждённых контакта` из hero профиля, потому что он считал наличие email/телефона, а не фактическую верификацию контактов.

## Затронутые файлы

- `frontend/app/pages/dashboard.vue`
- `frontend/app/pages/dashboard/[section].vue`
- `frontend/app/pages/admin/index.vue`
- `frontend/app/pages/admin/[section].vue`
- `frontend/app/assets/styles/main.css`
- `frontend/app/components/layout/AppHeader.vue`
- `frontend/app/pages/login.vue`
- `frontend/app/pages/register.vue`
- `frontend/app/pages/appeal/new.vue`

## Проверки

- `docker compose exec -T nuxt pnpm typecheck`
- `docker compose exec -T nuxt pnpm build`
- HTTP-проверки новых URL:
  - `https://rukadobra.localhost/dashboard/profile` -> `200`;
  - `https://rukadobra.localhost/dashboard/drafts` -> `200`;
  - `https://rukadobra.localhost/admin/categories` -> `200`;
  - `https://rukadobra.localhost/admin/news` -> `200`.

## Postman

Postman collection не обновлялась: API endpoints и контракт ответов не менялись.
