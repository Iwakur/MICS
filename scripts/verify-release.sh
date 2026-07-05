#!/usr/bin/env bash
set -Eeuo pipefail

APP_URL="${APP_URL:?APP_URL must be set, for example https://mics.example.com}"

php artisan app:check-production-readiness
curl --fail --silent --show-error --max-time 10 "${APP_URL%/}/up" >/dev/null
curl --fail --silent --show-error --max-time 10 "${APP_URL%/}/ready" >/dev/null
curl --fail --silent --show-error --max-time 10 "${APP_URL%/}/login" | grep --quiet 'Log in'

echo "Release verification passed for ${APP_URL}."
