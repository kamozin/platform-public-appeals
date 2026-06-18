# Отчет: Dockerized monorepo foundation

## Что сделано

- Создан `docker-compose.yml`.
- Добавлен `nginx` reverse proxy.
- Добавлен PHP-FPM контейнер `laravel`.
- Добавлен Node контейнер `nuxt`.
- Добавлен MySQL 8.4.
- Добавлен Redis.
- Добавлен Mailpit.
- Добавлен Gotenberg.
- Добавлен локальный HTTPS-конфиг для:
  - `https://rukadobra.localhost`;
  - `https://mail.rukadobra.localhost`.
- Добавлены скрипты генерации локальных сертификатов через `mkcert`.
- Добавлена документация по запуску окружения.
- Добавлены временные placeholder-ы для backend/frontend, чтобы reverse proxy проверялся до установки Laravel и Nuxt.

## Затронутые файлы

- `.env.example`
- `.gitignore`
- `docker-compose.yml`
- `docker/nginx/default.conf`
- `docker/nginx/certs/.gitignore`
- `docker/nginx/certs/README.md`
- `docker/php/Dockerfile`
- `docker/php/php.ini`
- `docker/php/www.conf`
- `docker/node/dev-placeholder/server.mjs`
- `app/public/index.php`
- `frontend/.gitkeep`
- `scripts/dev/generate-local-certs.sh`
- `scripts/dev/generate-local-certs.ps1`
- `docs/development/docker-local.md`

## Проверки

Выполнено:

```bash
docker compose config
docker compose build laravel
docker compose up -d
curl -k -I http://rukadobra.localhost/
curl -k -I https://rukadobra.localhost/
curl -k https://rukadobra.localhost/api/v1/health
curl -k https://mail.rukadobra.localhost/
docker compose exec -T laravel sh -lc "curl -s -o /dev/null -w '%{http_code}\n' http://gotenberg:3000/health"
```

Результат:

- `http://rukadobra.localhost/` возвращает `301` на HTTPS.
- `https://rukadobra.localhost/` возвращает `200`.
- `https://rukadobra.localhost/api/v1/health` проксируется в PHP-FPM и возвращает JSON placeholder.
- `https://mail.rukadobra.localhost/` открывает Mailpit UI.
- Laravel-контейнер видит `GOTENBERG_URL=http://gotenberg:3000`.
- Gotenberg `/health` возвращает `200`.

## Ограничения

На машине не установлен `mkcert`, поэтому для smoke-теста был временно сгенерирован self-signed сертификат через OpenSSL в `docker/nginx/certs/`. Эта директория исключена из git.

Для нормального trusted HTTPS в браузере нужно установить `mkcert` и выполнить:

```bash
scripts/dev/generate-local-certs.sh
```

или в PowerShell:

```powershell
.\scripts\dev\generate-local-certs.ps1
```

## Postman

Не требуется: в задаче нет продуктовых API endpoint-ов. Текущий `/api/v1/health` является временным placeholder до задачи `02-backend-api-foundation`.

