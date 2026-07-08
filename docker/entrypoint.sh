#!/bin/sh
set -eu

cd /var/www/html

mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is required. Set it in the runtime environment before starting the app container." >&2
    exit 1
fi

if [ "${APP_RUN_OPTIMIZE:-true}" = "true" ]; then
    php artisan optimize --ansi
fi

exec "$@"
