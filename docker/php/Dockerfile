FROM php:8.3-fpm

ARG APP_UID=1000
ARG APP_GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    bash \
    curl \
    default-mysql-client \
    git \
    libicu-dev \
    libonig-dev \
    libsqlite3-dev \
    libzip-dev \
    unzip \
    zip \
    && docker-php-ext-install \
    bcmath \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_mysql \
    pdo_sqlite \
    zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN groupmod -o -g ${APP_GID} www-data \
    && usermod -o -u ${APP_UID} -g www-data www-data

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-rukadobra.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html
