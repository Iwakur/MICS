#!/usr/bin/env bash
set -Eeuo pipefail

APP_ROOT="${APP_ROOT:?APP_ROOT must be set, for example /var/www/mics}"
RELEASE_TAG="${RELEASE_TAG:?RELEASE_TAG must be set to an immutable Git tag or commit}"
REPOSITORY="${REPOSITORY:-git@github.com:Iwakur/MICS.git}"
RELEASE_DIR="${APP_ROOT%/}/releases/${RELEASE_TAG//\//-}"

[[ ! -e "$RELEASE_DIR" ]] || { echo "Release already exists: $RELEASE_DIR" >&2; exit 2; }
mkdir -p "$RELEASE_DIR"
git -C "$RELEASE_DIR" init --quiet
git -C "$RELEASE_DIR" remote add origin "$REPOSITORY"
git -C "$RELEASE_DIR" fetch --quiet --depth 1 origin "$RELEASE_TAG"
git -C "$RELEASE_DIR" checkout --quiet --detach FETCH_HEAD
ln -sfn "${APP_ROOT%/}/shared/.env" "$RELEASE_DIR/.env"
rm -rf "$RELEASE_DIR/storage"
ln -sfn "${APP_ROOT%/}/shared/storage" "$RELEASE_DIR/storage"

cd "$RELEASE_DIR"
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci --ignore-scripts
npm run build
php artisan app:check-production-readiness --skip-database
php artisan down --retry=30 || true
php artisan migrate --force
php artisan app:check-production-readiness
php artisan optimize
ln -sfn "$RELEASE_DIR" "${APP_ROOT%/}/current.next"
mv -Tf "${APP_ROOT%/}/current.next" "${APP_ROOT%/}/current"
php artisan up

APP_URL="${APP_URL:?APP_URL must be set}" scripts/verify-release.sh
echo "Deployed $RELEASE_TAG to $RELEASE_DIR."
