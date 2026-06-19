# Backend-модерация обращений

## Context

Сейчас пользовательская подача обращения проходит через `appeal_drafts`.
После отправки черновик получает статус `pending_moderation`, но дальше нет полноценного рабочего процесса модератора:

- нет отдельной очереди submitted draft-ов для администратора;
- нет решений модерации: одобрить, запросить правки, отклонить;
- нет связи между исходным draft-ом и созданным публичным обращением;
- нет audit trail действий модератора;
- базовый admin CRUD `/api/v1/admin/appeals` работает напрямую с `public_appeals` и не закрывает сценарий проверки пользовательских заявок.

## Goal

Добавить backend API для модерации отправленных обращений:

- администратор видит очередь обращений на модерации;
- администратор открывает карточку draft-а с контактами и metadata вложений;
- администратор может запросить правки;
- администратор может отклонить обращение с причиной;
- администратор может одобрить обращение и атомарно создать публичную карточку в `public_appeals`;
- draft получает финальный moderation state и ссылку на `public_appeal_id`;
- действия модератора сохраняются в журнале.

## Non-goals

- Не строим Nuxt UI в этой задаче.
- Не меняем публичные URL `/appeals` и `/appeals/{slug}`.
- Не меняем Laravel на HTML-renderer.
- Не добавляем Blade, Inertia или Livewire.
- Не делаем полноценную RBAC-модель ролей.
- Не делаем публичную выдачу исходных вложений draft-а.
- Не переносим автоматически приватные контакты в публичное обращение.
- Не меняем исходную папку `static/`.

## Backend changes

- Добавить миграцию для moderation fields в `appeal_drafts`:
  - `moderated_by_user_id` nullable FK на `users`;
  - `moderated_at` nullable timestamp;
  - `moderation_note` nullable text;
  - `rejection_reason` nullable text;
  - `public_appeal_id` nullable FK на `public_appeals`.
- Добавить таблицу `appeal_moderation_events`:
  - `id` UUID;
  - `appeal_draft_id` FK;
  - `moderator_user_id` nullable FK;
  - `action` string;
  - `comment` nullable text;
  - `payload` nullable json;
  - timestamps.
- Расширить модель `AppealDraft` связями и casts.
- Добавить модель `AppealModerationEvent`.
- Добавить `AppealModerationService` в Application:
  - list/show moderation queue;
  - request changes;
  - reject;
  - approve and create `PublicAppeal` in transaction.
- При `request_changes` переводить draft в `needs_changes`.
- При повторной отправке draft из `needs_changes` переводить его обратно в `pending_moderation`.
- При `reject` переводить draft в `rejected`.
- При `approve` создавать `public_appeals`, связи публичной карточки и переводить draft в `approved`.
- Контроллеры должны быть тонкими, валидация только через FormRequest.
- Добавить централизованные error codes/messages, если появятся новые бизнес-ошибки.

## Frontend changes

Frontend в этой задаче не меняется.
Nuxt UI будет отдельной атомарной задачей.

## API contract

Новые endpoints:

- `GET /api/v1/admin/appeal-moderation`
  - route name: `api.admin.appeal-moderation.index`
  - query: `status`, `page`, `per_page`
  - default status: `pending_moderation`
- `GET /api/v1/admin/appeal-moderation/{id}`
  - route name: `api.admin.appeal-moderation.show`
  - `id` должен быть UUID.
- `POST /api/v1/admin/appeal-moderation/{id}/request-changes`
  - route name: `api.admin.appeal-moderation.request-changes`
  - body: `{ "message": "..." }`
- `POST /api/v1/admin/appeal-moderation/{id}/reject`
  - route name: `api.admin.appeal-moderation.reject`
  - body: `{ "reason": "..." }`
- `POST /api/v1/admin/appeal-moderation/{id}/approve`
  - route name: `api.admin.appeal-moderation.approve`
  - body: payload публичного обращения, совместимый по смыслу с `AdminAppealRequest`.

Ответ `GET show` должен включать:

- данные draft-а;
- contact fields для администратора;
- metadata вложений: `id`, `kind`, `originalName`, `mimeType`, `size`;
- текущий moderation state;
- `moderationNote`;
- `rejectionReason`;
- `publicAppealId`;
- `events[]`.

Ошибки:

- `UNAUTHORIZED` для отсутствующего bearer token.
- `FORBIDDEN` для пользователя без admin-доступа.
- `NOT_FOUND` для отсутствующего draft-а.
- `CONFLICT` для недопустимого перехода статуса.
- `VALIDATION_FAILED` для ошибок FormRequest.

## SEO impact

Публичные SEO-страницы не меняются.
Admin endpoints не участвуют в sitemap.
Публичная карточка попадает в sitemap только после одобрения и создания `public_appeals.is_public = true`.

## Tests

- Feature tests:
  - обычный пользователь не имеет доступа к moderation endpoints;
  - admin видит `pending_moderation` draft в очереди;
  - admin видит detail draft-а с вложениями metadata;
  - admin может запросить правки, draft получает `needs_changes`;
  - пользователь может отредактировать `needs_changes` и повторно отправить в `pending_moderation`;
  - admin может отклонить draft, draft получает `rejected`;
  - admin может одобрить draft, создаётся `public_appeal`, draft получает `approved` и `public_appeal_id`;
  - публичная карточка доступна через `GET /api/v1/appeals/{slug}` только после approve.
- Route tests:
  - все новые routes именованы;
  - UUID constraints работают.
- Проверки:
  - `composer test`;
  - `composer lint` или доступный эквивалент проекта.

## Acceptance criteria

- [ ] Submitted draft-ы можно получить через admin moderation queue.
- [ ] Контакты заявителя видны только admin moderation API и не попадают в публичное обращение.
- [ ] Admin action `request-changes` сохраняет сообщение и переводит draft в `needs_changes`.
- [ ] Draft в `needs_changes` можно повторно отправить на модерацию.
- [ ] Admin action `reject` сохраняет причину и переводит draft в `rejected`.
- [ ] Admin action `approve` в транзакции создаёт публичное обращение и связывает его с draft-ом.
- [ ] Повторное approve/reject/request-changes для финальных статусов возвращает `CONFLICT`.
- [ ] Для всех новых endpoints есть FormRequest, route names и tests.
- [ ] Postman collection обновлена.

## Postman

Обновить `docsai/postmancollection/rukadobra-api.postman_collection.json`:

- добавить папку `Admin Appeal Moderation`;
- добавить запросы list/show/request changes/reject/approve;
- добавить тесты на сохранение `moderation_draft_id` и `moderated_public_appeal_id`.
