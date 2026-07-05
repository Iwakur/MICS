#!/usr/bin/env bash
set -Eeuo pipefail

APP_ROOT="${APP_ROOT:?APP_ROOT must be set}"
TARGET_RELEASE="${TARGET_RELEASE:?TARGET_RELEASE must be the existing release directory name}"
TARGET="${APP_ROOT%/}/releases/$TARGET_RELEASE"
[[ -d "$TARGET" ]] || { echo "Release not found: $TARGET" >&2; exit 2; }

cd "$TARGET"
php artisan app:check-production-readiness
ln -sfn "$TARGET" "${APP_ROOT%/}/current.next"
mv -Tf "${APP_ROOT%/}/current.next" "${APP_ROOT%/}/current"
php artisan optimize
APP_URL="${APP_URL:?APP_URL must be set}" scripts/verify-release.sh
echo "Rolled application code back to $TARGET_RELEASE. Database migrations were not reversed."
