# syntax=docker/dockerfile:1.7

FROM node:22-bookworm-slim AS node-runtime

RUN npm install --global pm2@latest

FROM php:8.3-fpm-bookworm AS php-base

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install --no-install-recommends --yes \
        curl \
        git \
        libcurl4-openssl-dev \
        libicu-dev \
        libonig-dev \
        libpq-dev \
        libsqlite3-dev \
        libzip-dev \
        nginx \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        intl \
        mbstring \
        pcntl \
        pdo_pgsql \
        pdo_sqlite \
        sockets \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=node-runtime /usr/local/ /usr/local/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

FROM php-base AS build

ARG VITE_REVERB_APP_KEY=chat-aviso-key
ENV VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY}

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN composer dump-autoload --classmap-authoritative --no-dev \
    && php artisan package:discover --ansi \
    && npm run build \
    && rm -rf node_modules

FROM php-base AS production

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PHP_OPCACHE_ENABLE=1

COPY --from=build --chown=www-data:www-data /var/www/html /var/www/html
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-chat-aviso.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-chat-aviso.conf
COPY docker/ecosystem.config.cjs /etc/pm2/ecosystem.config.cjs
COPY docker/entrypoint.sh /usr/local/bin/chat-aviso-entrypoint

RUN chmod +x /usr/local/bin/chat-aviso-entrypoint \
    && mkdir -p /run/nginx \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=45s --retries=3 \
    CMD curl --fail --silent http://127.0.0.1/up > /dev/null || exit 1

ENTRYPOINT ["chat-aviso-entrypoint"]
CMD ["pm2-runtime", "/etc/pm2/ecosystem.config.cjs"]
