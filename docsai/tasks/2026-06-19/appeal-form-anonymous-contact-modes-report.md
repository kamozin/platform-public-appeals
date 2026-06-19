# Отчёт: режимы подачи обращения

## Что сделано

- Форма `/appeal/new` переведена на два способа подачи: инкогнито и с контактными данными.
- Инкогнито-подача требует минимум одно вложение: фото, видео или документ.
- Контактная подача требует ФИО и минимум один канал связи: телефон или email.
- При инкогнито-подаче контактный блок скрыт, контактные поля отправляются как `null`, а предложение личного кабинета не показывается.
- Result screen и блок "Что дальше?" показывают разные тексты для инкогнито и контактного сценария.

## Затронутые файлы

- `frontend/app/pages/appeal/new.vue`
- `frontend/app/assets/styles/main.css`
- `docs/development/frontend-private-workflows.md`
- `docsai/tasks/2026-06-19/appeal-form-anonymous-contact-modes.md`
- `docsai/tasks/2026-06-19/appeal-form-anonymous-contact-modes-report.md`

## Проверки

Выполнено:

- `rg`-проверка отсутствия старых frontend-режимов `public` и `account` в `/appeal/new`;
- `git diff --check` для изменённых файлов.

Ограничения окружения:

- `pnpm` недоступен в Windows PATH, Corepack падает на проверке подписи, WSL содержит Node `12.22.9` и не содержит `pnpm`;
- временный запуск `vue-tsc` через `npx` показал существующие ошибки типизации по разным frontend-файлам и неполный Nuxt/Volar resolution без локального pnpm окружения;
- временный запуск ESLint через `npx` остановился на `ERR_MODULE_NOT_FOUND: ohash` внутри текущей `.pnpm`-структуры.

## Postman

Postman collection не обновлялась: API endpoints не менялись.
