# Отчет: улучшение главного hero-слайдера

## Что сделано

- Переделана структура `HomeHero.vue`: фон каждого слайда теперь отдельный слой, а сменяемый текст находится в фиксированной `hero-copy-frame`.
- Добавлены crossfade/motion для фоновых слоев и мягкая transition-анимация текста.
- Телефон и CTA-кнопки оставлены прежними, но вынесены из анимируемого текстового слоя, чтобы не прыгать при смене слайда.
- Добавлено автопереключение с паузой при hover/focus и отключением при `prefers-reduced-motion: reduce`.
- Улучшены точки слайдера: активная точка стала progress-индикатором.
- Увеличена desktop-высота hero-блока и добавлены mobile-ограничения, чтобы контент и кнопки не обрезались.
- Пересобрана схема затемнения: вместо общей серой пленки используется светлый reading-layer слева и мягкая синяя глубина справа/снизу.
- `hero-copy-frame` переведен с `min-height` на фиксированный `height` на desktop/mobile, чтобы телефон и CTA не сдвигались при смене текста.
- Верхний `hero-label` получил `justify-self: start`, чтобы бейдж не растягивался на всю ширину copy-frame.
- Hero-слайдер пересобран как новая композиция: слева стабильная светлая контентная зона, справа отдельная media-зона с фото, диагональной гранью и собственным затемнением.
- Индикатор слайдов перенесен в центр media-зоны на desktop и возвращается в центр hero-блока на mobile.
- Исправлен clipping первого `hero-note`: фиксированная desktop-область copy увеличена, note-карточка расширена, а текст внутри слегка уплотнен без изменения самой текстовки.
- Индикатор слайдов сделан белым и закреплен в правом нижнем углу hero-слайдера на desktop/mobile.
- Индикатор оставлен только точками: фон, border, тень, blur-подложка и скругленная капсула удалены.
- На весь hero-слайдер добавлен общий cinematic/vignette-эффект через внутренние тени и overlay под контентом, чтобы баннер выглядел глубже без затемнения текста.
- Добавлен отдельный `hero-effects` слой: диагональный animated light-sweep, постоянные glass-штрихи, тонкая сетка и нижняя красно-синяя световая линия под контентом.
- Декоративная карта России удалена из hero-слайдера: визуально не подошла композиции.

## Затронутые файлы

- `frontend/app/components/home/HomeHero.vue`
- `frontend/app/assets/styles/main.css`
- `docs/development/frontend-home-page.md`
- `docsai/tasks/2026-06-18/enhance-home-hero-slider.md`
- `docsai/tasks/2026-06-18/enhance-home-hero-slider-report.md`

## Проверки

Успешно:

- `docker compose exec -T nuxt sh -lc "./node_modules/.bin/vue-tsc --noEmit -p tsconfig.json"`
- `curl.exe -I -k https://rukadobra.localhost/`
- SSR smoke: в HTML главной найдены `home-hero-wrap`, `hero-media-stack`, `hero-copy-frame`.
- `git diff --check -- frontend/app/assets/styles/main.css frontend/app/components/home/HomeHero.vue`

Не завершились из-за окружения зависимостей:

- `docker compose exec -T nuxt pnpm lint`
- `docker compose exec -T nuxt pnpm typecheck`
- `docker compose exec -T nuxt pnpm build`

Причина: внутри контейнера установлены `linux-x64-gnu` native bindings, а runtime ищет `linux-x64-musl`, например `@oxc-parser/binding-linux-x64-musl`. Это происходит до анализа кода/сборки приложения.

## Postman

Postman collection не обновлялась: API endpoints и контракты не менялись.
