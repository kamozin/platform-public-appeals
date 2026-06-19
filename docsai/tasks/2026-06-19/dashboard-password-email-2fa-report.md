# Отчёт: смена пароля и email-2FA в личном кабинете

## Что сделано

- Добавлена смена пароля авторизованного пользователя через `PATCH /api/v1/profile/password`.
- Добавлено включение email-2FA через код на текущий email пользователя.
- Добавлено отключение email-2FA через текущий пароль.
- Изменён login flow: пользователи с включённой email-2FA получают challenge после логина/пароля и bearer token только после `POST /api/v1/auth/2fa/verify`.
- Challenge для 2FA привязан к пользователю и помечается использованным через `consumed_at`.
- Проверка принадлежности challenge пользователю выполняется до проверки кода, чтобы чужой challenge нельзя было расходовать или накручивать по нему попытки.
- Password reset challenge теперь также проверяет email до расходования кода.
- Код подтверждения закреплён как `123456` для всех окружений проекта.
- Email-коды отправляются через брендированный HTML-шаблон письма.
- В `/dashboard` во вкладке безопасности добавлены формы смены пароля и управления email-2FA.
- Формы безопасности перенесены из inline-блоков в модальные окна: на странице остались компактные карточки действий и статусы.

## Затронутые файлы

- `database/migrations/2026_06_19_000003_add_email_two_factor_to_users.php`
- `app/Models/User.php`
- `app/Models/VerificationChallenge.php`
- `app/Application/Auth/AuthService.php`
- `app/Application/Auth/VerificationService.php`
- `app/Application/Profile/ProfileSecurityService.php`
- `app/Notifications/Auth/VerificationCodeNotification.php`
- `app/Http/Controllers/Api/Auth/AuthController.php`
- `app/Http/Controllers/Api/Profile/ProfileSecurityController.php`
- `app/Http/Requests/Api/Auth/TwoFactorVerifyRequest.php`
- `app/Http/Requests/Api/Profile/PasswordUpdateRequest.php`
- `app/Http/Requests/Api/Profile/EmailTwoFactorSendRequest.php`
- `app/Http/Requests/Api/Profile/EmailTwoFactorEnableRequest.php`
- `app/Http/Requests/Api/Profile/EmailTwoFactorDisableRequest.php`
- `routes/api.php`
- `lang/en/api_errors.php`
- `lang/ru/api_errors.php`
- `resources/views/emails/auth/verification-code.blade.php`
- `frontend/app/composables/useAuth.ts`
- `frontend/app/types/api/private.ts`
- `frontend/app/pages/login.vue`
- `frontend/app/pages/dashboard.vue`
- `frontend/app/assets/styles/main.css`
- `docs/development/frontend-private-workflows.md`
- `tests/Feature/Api/AuthEndpointTest.php`
- `tests/Feature/Api/DashboardProfileEndpointTest.php`
- `tests/Feature/Api/VerificationEndpointTest.php`
- `docs/development/private-workflows-api.md`
- `docsai/postmancollection/rukadobra-api.postman_collection.json`

## Проверки

- `docker compose exec -T laravel php artisan test tests/Feature/Api/AuthEndpointTest.php tests/Feature/Api/DashboardProfileEndpointTest.php tests/Feature/Api/VerificationEndpointTest.php` — пройдено, 13 tests / 96 assertions.
- После добавления HTML-письма повторно пройдено `docker compose exec -T laravel php artisan test tests/Feature/Api/AuthEndpointTest.php tests/Feature/Api/DashboardProfileEndpointTest.php tests/Feature/Api/VerificationEndpointTest.php` — 14 tests / 104 assertions.
- `docker compose exec -T laravel php artisan test` — пройдено, 54 tests / 311 assertions.
- После фиксации кода подтверждения и HTML-письма повторно пройдено `docker compose exec -T laravel php artisan test` — 55 tests / 319 assertions.
- `docker compose exec -T laravel vendor/bin/phpstan analyse` — пройдено без ошибок.
- После добавления HTML-письма повторно пройдено `docker compose exec -T laravel vendor/bin/phpstan analyse --no-progress` — пройдено без ошибок.
- `docker compose exec -T nuxt pnpm lint` — пройдено.
- `docker compose exec -T nuxt pnpm typecheck` — пройдено.
- После переноса форм безопасности в модальные окна повторно пройдены `docker compose exec -T nuxt pnpm lint` и `docker compose exec -T nuxt pnpm typecheck`.
- `docker compose exec -T laravel php artisan migrate --force` — пройдено, новых миграций к применению нет.
- `docker compose exec -T laravel php -r "json_decode(...)"` — Postman collection валидна как JSON.
- `git diff --check` по затронутым backend/docs файлам — пройдено.
- Локальный WSL-запуск `php artisan test ...` не выполняется вне контейнера из-за отсутствия `pdo_sqlite`.
- Локальный WSL-запуск `pnpm lint` не выполняется вне контейнера, потому что `pnpm` отсутствует в `PATH`.

## Postman

- Коллекция обновлена новыми запросами для смены пароля, включения/отключения email-2FA и проверки второго фактора при входе.
- Для фиксированного кода `123456` и HTML-письма новые endpoints не добавлялись, поэтому отдельное обновление коллекции не требуется.
