# syntax=docker/dockerfile:1.7

ARG PHP_VERSION=8.4
ARG NODE_VERSION=22

FROM php:${PHP_VERSION}-fpm-alpine AS php-base

WORKDIR /var/www/html

RUN apk add --no-cache \
        caddy \
        icu-data-full \
        icu-libs \
        libpq \
        supervisor \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        postgresql-dev \
    && docker-php-ext-install \
        bcmath \
        intl \
        pdo_pgsql \
    && docker-php-ext-enable opcache \
    && apk del .build-deps

FROM php-base AS vendor-build

RUN apk add --no-cache unzip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY routes ./routes
COPY artisan ./

RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

FROM node:${NODE_VERSION}-alpine AS frontend-build

WORKDIR /frontend

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM php-base AS app-runtime

ENV APP_ENV=production
ENV APP_DEBUG=false

COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY lang ./lang
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY artisan ./
COPY composer.json ./
COPY --from=vendor-build /var/www/html/vendor ./vendor
COPY --from=frontend-build /frontend/public/build ./public/build
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-mics.ini
COPY docker/entrypoint.sh /usr/local/bin/mics-entrypoint
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x /usr/local/bin/mics-entrypoint

USER www-data

ENTRYPOINT ["/usr/local/bin/mics-entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
