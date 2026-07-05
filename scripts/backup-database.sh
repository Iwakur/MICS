#!/usr/bin/env bash
set -Eeuo pipefail

BACKUP_DIR="${BACKUP_DIR:?BACKUP_DIR must be set}"
RELEASE="${RELEASE_TAG:-manual-$(date -u +%Y%m%dT%H%M%SZ)}"
mkdir -p "$BACKUP_DIR"
umask 077

: "${DB_HOST:?DB_HOST must be set}" "${DB_PORT:?DB_PORT must be set}" "${DB_DATABASE:?DB_DATABASE must be set}" "${DB_USERNAME:?DB_USERNAME must be set}"
OUTPUT="${BACKUP_DIR%/}/mics-${RELEASE}.dump"
PGPASSWORD="${DB_PASSWORD:-}" pg_dump --format=custom --no-owner --no-acl --host="$DB_HOST" --port="$DB_PORT" --username="$DB_USERNAME" --file="$OUTPUT" "$DB_DATABASE"
pg_restore --list "$OUTPUT" >/dev/null
echo "Verified PostgreSQL backup: $OUTPUT"
