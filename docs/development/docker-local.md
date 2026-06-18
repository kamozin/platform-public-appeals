# Локальное Docker-окружение

## Состав

Локальное окружение запускается через Docker Compose:

- `nginx` - единая HTTPS-точка входа и reverse proxy;
- `laravel` - PHP-FPM контейнер для Laravel JSON API;
- `nuxt` - Node контейнер для Nuxt SSR;
- `mysql` - MySQL 8.4;
- `redis` - Redis;
- `mailpit` - локальная SMTP-почта и web inbox;
- `gotenberg` - сервис генерации PDF.

## Локальные домены

Добавьте в hosts:

```txt
127.0.0.1 rukadobra.localhost
127.0.0.1 mail.rukadobra.localhost
```

## HTTPS

Для локального HTTPS используется `mkcert`.

```bash
scripts/dev/generate-local-certs.sh
```

На Windows можно выполнить:

```powershell
.\scripts\dev\generate-local-certs.ps1
```

Сертификаты появляются в `docker/nginx/certs/` и не должны попадать в git.

## Запуск

```bash
docker compose up -d --build
```

Основной сайт:

```txt
https://rukadobra.localhost
```

Mailpit:

```txt
https://mail.rukadobra.localhost
```

HTTP всегда редиректит на HTTPS:

```txt
http://rukadobra.localhost -> https://rukadobra.localhost
```

## Routing

Публичная схема через nginx:

```txt
/api/* -> laravel:9000 PHP-FPM
/*     -> nuxt:3000 SSR
```

Для server-side Nuxt запросов к Laravel используется внутренний nginx listener без внешнего проброса порта:

```txt
http://nginx:8080/api/v1
```

Это значение задается в `NUXT_API_INTERNAL_BASE`. Браузерные запросы используют публичное значение:

```txt
NUXT_PUBLIC_API_BASE=/api/v1
```

## Внутренние сервисы

Gotenberg доступен только внутри Docker-сети:

```txt
http://gotenberg:3000
```

Mailpit SMTP доступен Laravel внутри Docker-сети:

```txt
mailpit:1025
```

## Быстрые проверки

```bash
docker compose config --quiet
curl -k https://rukadobra.localhost/api/v1/health
curl -k https://rukadobra.localhost/
```
