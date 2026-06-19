# Отчёт: уточнение переноса формы обращения из static

## Что сделано

- Исправлен первый шаг `/appeal/new`: блок `Тип обращения` заменён на исходный блок `Способ подачи обращения` из `static/appeal.html`.
- Сетка категорий приведена к исходному составу из static: добавлены `СВО / ветераны` и `Молодёжные банды`.
- Для категорий возвращены исходные SVG-иконки из static sprite.
- Правая информационная карточка приведена к исходному контенту и изображению `verification-illustration.png`.

## Затронутые файлы

- `frontend/app/pages/appeal/new.vue`
- `frontend/app/components/layout/AppIcon.vue`
- `frontend/app/components/layout/AppSvgSprite.vue`

## Проверки

- `pnpm typecheck` в `node:22-bookworm` Docker-контейнере.
- `pnpm lint` в `node:22-bookworm` Docker-контейнере.

## Postman

Postman collection не изменялась: API-контракт и endpoints не менялись.
