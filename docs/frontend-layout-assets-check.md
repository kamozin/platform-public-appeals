# Проверка общей frontend-верстки и ассетов

## Что сделано

- Auth layout переведён на общий `AppHeader` и `AppFooter`, чтобы шапка и подвал были одинаковыми между публичными, auth и private страницами.
- Экран подтверждения контакта приведён к общей auth/screen-структуре с иллюстрацией, состояниями отправки кода и подтверждения.
- Личный кабинет переведён на существующую `cabinet-*` сетку: sidebar, hero профиля, статистика, панели списков и уведомлений.
- Добавлен общий fallback для изображений из API, чтобы пустой или битый URL не показывал сломанную картинку браузера.
- Добавлены недостающие публичные ассеты `verification-illustration.png` и `welcome-illustration.png` в `frontend/public/assets`.
- Добавлен общий CSS-слой для текущих Nuxt-страниц `content-page`, `content-hero`, `public-section`, списков обращений, статей, 404 и pagination.
- Исправлены стили социальных кнопок в footer: текущая разметка использует `button`, а стили были только для `a`.

## Затронутые файлы

- `frontend/app/layouts/auth.vue`
- `frontend/app/composables/useImageFallback.ts`
- `frontend/app/pages/verification.vue`
- `frontend/app/pages/dashboard.vue`
- `frontend/app/pages/appeals/index.vue`
- `frontend/app/pages/appeals/[slug].vue`
- `frontend/app/pages/news/index.vue`
- `frontend/app/pages/news/[slug].vue`
- `frontend/app/assets/styles/main.css`
- `frontend/public/assets/verification-illustration.png`
- `frontend/public/assets/welcome-illustration.png`
- `docsai/tasks/2026-06-18/fix-global-frontend-layout-assets.md`

## Проверки

- `nuxt prepare`
- `vue-tsc --noEmit -p tsconfig.json`
- `eslint .`
- `nuxt build`
- Production server на `http://127.0.0.1:3010/`
- HTTP-проверки страниц `/`, `/book`, `/login`, `/register`, `/password-reset`, `/verification`, `/dashboard`
- HTTP-проверки ассетов `/assets/verification-illustration.png`, `/assets/welcome-illustration.png`, `/assets/logo-ruka-dobra.svg`

## Postman

Не обновлялся: API endpoints и контракты не менялись.

## Примечания

`static/` не изменялся. Иллюстрации скопированы из `static/assets` в публичную папку Nuxt.
