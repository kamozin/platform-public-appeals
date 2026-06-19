# Симметричная сетка публичного layout

## Context

Во frontend уже есть общий `default` layout, `AppHeader`, `AppFooter` и глобальный `.container`, но страницы и секции используют разные локальные оболочки и отступы. Из-за этого визуальная ось header, основного контента и footer может восприниматься несогласованной: главная, публичные страницы, реестр обращений и контентные страницы не всегда выглядят собранными в одну симметричную сетку.

Задача нужна, чтобы привести публичную часть сайта к единой геометрии без изменения исходной папки `static/` и без изменения backend API.

## Goal

Сделать единый shell-контейнер и правила отступов для публичного frontend так, чтобы header, main-контент и footer были визуально выровнены по одной сетке на главной и остальных публичных страницах.

После выполнения:

- header, основной контент и footer имеют общий `max-width` и одинаковые боковые gutters;
- главная страница не выбивается из общей сетки;
- публичные страницы `/book`, `/privacy`, `/agreement`, `/categories`, `/appeals`, `/appeals/:slug`, `/news`, `/news/:slug` сохраняют SSR и получают согласованную ширину;
- локальные отступы секций не ломают общую симметрию shell;
- mobile/tablet/desktop состояния остаются без горизонтального скролла и обрезания текста.

## Non-goals

- Не менять исходники в `static/`.
- Не менять backend API, DTO, SEO-контракты, sitemap и robots.
- Не переделывать визуальный стиль сайта с нуля.
- Не менять состав блоков, тексты, изображения, CTA и маршруты страниц.
- Не добавлять Blade UI, Laravel web UI, Inertia или Livewire.
- Не решать в этой задаче отдельные проблемы конкретных карточек, форм или данных из API, если они не связаны с общей сеткой.

## Backend changes

Backend не меняется.

## Frontend changes

- Проверить `frontend/app/layouts/default.vue` и оставить единый публичный shell через `LayoutAppHeader`, `main.app-main`, `LayoutAppFooter`.
- Обновить `frontend/app/assets/styles/main.css`:
  - привести `.container` и shell-переменные к единому источнику ширины и gutters;
  - убрать дублирующие или конфликтующие page-specific правила ширины для header/footer;
  - синхронизировать desktop/tablet/mobile отступы header, content и footer;
  - сохранить текущую визуальную плотность блоков, но убрать несогласованные боковые смещения.
- Проверить публичные страницы и компоненты главной:
  - `frontend/app/pages/index.vue`;
  - `frontend/app/components/home/*`;
  - `frontend/app/pages/book.vue`;
  - `frontend/app/pages/privacy.vue`;
  - `frontend/app/pages/agreement.vue`;
  - `frontend/app/pages/categories.vue`;
  - `frontend/app/pages/appeals/index.vue`;
  - `frontend/app/pages/appeals/[slug].vue`;
  - `frontend/app/pages/news/index.vue`;
  - `frontend/app/pages/news/[slug].vue`.
- Если нужен небольшой общий helper-класс для внутренних page-shell блоков, добавить его в глобальные стили и использовать точечно, без новой абстракции ради абстракции.

## API contract

API-контракт не меняется.

Новые endpoints не добавляются, существующие `/api/v1/...` не меняются, Postman collection не требует обновления.

## SEO impact

SEO-данные не меняются.

Публичные страницы должны остаться SSR/SWR/prerender согласно текущей конфигурации. Основной HTML-контент, `title`, `description`, canonical, Open Graph и robots не должны переехать в client-only логику.

## Tests

- [x] Запустить frontend typecheck.
- [x] Запустить frontend lint.
- [x] Запустить frontend build.
- [x] Проверить SSR smoke для `/`, `/book`, `/appeals`, `/news`.
- [ ] Визуально проверить desktop/tablet/mobile ширины:
  - нет горизонтального скролла;
  - header, main и footer стоят на одной сетке;
  - кнопки и длинные тексты не обрезаются;
  - мобильное меню не перекрывает контент некорректно.

## Acceptance criteria

- [x] Header, main-контент и footer используют согласованные `max-width` и боковые отступы.
- [x] Главная страница визуально выровнена с header и footer через общий `.container`.
- [x] Остальные публичные страницы не выбиваются из общей сетки на уровне shell-контейнера.
- [x] На ширинах mobile/tablet/desktop shell gutters задаются едиными переменными.
- [x] SSR и SEO meta публичных страниц не ухудшены.
- [x] `static/` не изменялась.
- [x] Backend и API-контракты не изменялись.
- [x] Проверки пройдены либо причина блокера явно описана в report-файле.

## Postman

Postman collection обновлять не нужно, потому что API endpoints и контракты не меняются.
