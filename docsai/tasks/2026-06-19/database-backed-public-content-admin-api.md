# Контент из базы и базовый admin API

## Context

Публичные категории, новости, обращения, часть комментариев и sitemap сейчас формируются из массивов внутри backend-сервиса. Это мешает управлять контентом из админки и приводит к расхождению между публичным frontend, API и фактическими данными проекта.

## Goal

Перевести публичный контент на чтение из базы данных и подготовить базовые API для администрирования категорий, новостей и публичных обращений.

## Non-goals

- Не строим полноценный визуальный Nuxt admin UI в этой задаче.
- Не меняем публичные URL frontend-страниц.
- Не меняем HTML-rendering на стороне Laravel.
- Не трогаем исходную папку `static/`.
- Не добавляем Inertia, Livewire или Blade UI.

## Backend changes

- Добавить таблицы для групп категорий, категорий, новостей, публичных обращений, вложений, таймлайна, документов и официального ответа.
- Добавить Eloquent-модели для инфраструктурного слоя чтения/записи контента.
- Переписать публичный content service на чтение из БД.
- Добавить базовый admin API для CRUD категорий, новостей и обращений.
- Добавить флаг администратора для пользователей и проверку доступа к `/api/v1/admin/*`.
- Наполнить сиды демонстрационными данными, которые заменяют текущие hardcoded массивы.

## Frontend changes

Существующие публичные страницы продолжают ходить в текущие API endpoint-ы через `useApi`. Визуальная админка Nuxt будет отдельной задачей.

## API contract

Публичные endpoint-ы сохраняют текущий контракт:

- `GET /api/v1/categories`
- `GET /api/v1/news`
- `GET /api/v1/news/{slug}`
- `GET /api/v1/appeals`
- `GET /api/v1/appeals/{slug}`
- `GET /api/v1/appeals/{appeal}/comments`
- `GET /api/v1/seo/sitemap-urls`

Новые admin endpoint-ы:

- `GET /api/v1/admin/categories`
- `POST /api/v1/admin/categories`
- `PATCH /api/v1/admin/categories/{id}`
- `DELETE /api/v1/admin/categories/{id}`
- `GET /api/v1/admin/news`
- `POST /api/v1/admin/news`
- `PATCH /api/v1/admin/news/{id}`
- `DELETE /api/v1/admin/news/{id}`
- `GET /api/v1/admin/appeals`
- `POST /api/v1/admin/appeals`
- `PATCH /api/v1/admin/appeals/{id}`
- `DELETE /api/v1/admin/appeals/{id}`

Ошибки:

- `UNAUTHORIZED` для отсутствующего токена.
- `FORBIDDEN` для пользователя без admin-доступа.
- `NOT_FOUND` для отсутствующей сущности.
- `VALIDATION_FAILED` для ошибок валидации.

## SEO impact

SEO DTO публичных страниц сохраняется. `sitemap-urls` теперь строится по опубликованным новостям и публичным обращениям из БД.

## Tests

- Обновить существующие feature-тесты публичного контента так, чтобы они проходили на БД-данных.
- Добавить тесты admin API для авторизации, CRUD и проверки route names.
- Запустить `composer test`.

## Acceptance criteria

- Публичные API больше не зависят от hardcoded массивов.
- Сиды создают стартовый управляемый контент.
- Admin API позволяет создать/обновить/удалить категории, новости и обращения.
- Обычный пользователь не имеет доступа к admin API.
- Публичный frontend продолжает работать через текущие endpoint-ы.
- Sitemap отдаёт URL из БД.

## Postman

Обновить `docsai/postmancollection/rukadobra-api.postman_collection.json`: добавить admin endpoints и сохранить существующие публичные запросы.
