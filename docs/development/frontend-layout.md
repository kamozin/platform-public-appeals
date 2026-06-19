# Frontend layout и общие UI-компоненты

## Назначение

Общий Nuxt layout переносит базовый shell из `static/` без переноса контента конкретных страниц. Laravel по-прежнему отдаёт только JSON API, а SSR, навигация, SEO-мета и визуальная оболочка остаются в Nuxt.

## Основные файлы

- `frontend/app/layouts/default.vue` — общий layout приложения.
- `frontend/app/components/layout/AppHeader.vue` — шапка сайта с desktop/mobile навигацией.
- `frontend/app/components/layout/AppFooter.vue` — общий footer с навигацией, контактами и подпиской без API-интеграции.
- `frontend/app/components/layout/AppLogo.vue` — логотип для header/footer.
- `frontend/app/components/layout/AppSvgSprite.vue` — общий SVG sprite для shell-иконок.
- `frontend/app/components/ui/ScrollTopButton.vue` — кнопка возврата наверх.
- `frontend/app/components/ui/PasswordField.vue` — поле пароля с переключением видимости.
- `frontend/app/components/ui/Pagination.vue` — базовая пагинация списков.
- `frontend/app/components/ui/ShareModal.vue` — общий modal для шаринга ссылки.
- `frontend/app/assets/styles/main.css` — общие tokens, shell-стили и стили общих UI-состояний.

## Assets

Нужные общие assets скопированы из `static/assets` в `frontend/public/assets`:

- `favicon.svg`
- `hero-russian-ribbon.svg`
- `logo-ruka-dobra.svg`

Папка `static/` остаётся read-only источником исходной вёрстки.

## Навигация

Header/footer используют `NuxtLink` и URL без `.html`. Для будущих маршрутов, страницы которых будут добавлены в следующих атомарных задачах, используется `external`, чтобы Nuxt SSR не пытался резолвить ещё не существующие `pages` и не писал router warnings.

Когда соответствующие страницы будут реализованы, `external` можно убрать точечно вместе с задачей страницы.

`AppHeader` является единственным компонентом шапки для `default` и `auth` layout-ов. Правые действия header везде одинаковые: `Войти` и `Подать обращение`. Отдельный `AuthHeader` не используется, чтобы публичные и auth-страницы не расходились по разметке и порядку действий.

## Сетка shell

Публичные страницы и блоки главной используют общий `.container` из `frontend/app/assets/styles/main.css`. Этот контейнер привязан к shell-переменным:

- `--shell-max-width`
- `--shell-gutter-current`
- `--shell-gutter`
- `--shell-gutter-tablet`
- `--shell-gutter-mobile`
- `--shell-gutter-small`

Header, основной контент и footer должны выравниваться по этой же сетке. Если странице нужен внутренний layout, он должен жить внутри `.container`, а не задавать другую внешнюю ширину страницы.

## Проверки

Базовые проверки выполняются из корня проекта:

```bash
docker compose run --rm nuxt sh -lc "corepack enable && corepack prepare pnpm@11.7.0 --activate && pnpm typecheck && pnpm lint && pnpm build"
```

Локальный стенд:

```bash
docker compose up -d
```

URL для проверки:

```txt
https://rukadobra.localhost/
```

## Desktop-хедер

Для общего header/footer chrome используется отдельная ширина `--shell-chrome-max-width`.
Она шире основного контентного контейнера, потому что единый `AppHeader` в авторизованном состоянии показывает больше действий справа: `Кабинет` или `Админка`, `Подать обращение`, `Выйти`.

Desktop-верстка хедера должна оставаться без наложений между логотипом, навигацией и правыми действиями.
Если полный набор элементов не помещается, `.shell-header` переключается в hamburger-меню на промежуточных ширинах до появления переполнения.
