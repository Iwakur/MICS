#!/usr/bin/env bash
set -Eeuo pipefail

BACKUP_FILE="${1:?Usage: RESTORE_CONFIRM=YES scripts/restore-database.sh /path/to/backup.dump}"
[[ "${RESTORE_CONFIRM:-}" == "YES" ]] || { echo 'Refusing restore: set RESTORE_CONFIRM=YES.' >&2; exit 2; }
[[ -f "$BACKUP_FILE" ]] || { echo "Backup not found: $BACKUP_FILE" >&2; exit 2; }
: "${DB_HOST:?DB_HOST must be set}" "${DB_PORT:?DB_PORT must be set}" "${DB_DATABASE:?DB_DATABASE must be set}" "${DB_USERNAME:?DB_USERNAME must be set}"

PGPASSWORD="${DB_PASSWORD:-}" pg_restore --clean --if-exists --no-owner --no-acl --exit-on-error --host="$DB_HOST" --port="$DB_PORT" --username="$DB_USERNAME" --dbname="$DB_DATABASE" "$BACKUP_FILE"
echo "Restore completed into $DB_DATABASE. Run scripts/verify-release.sh next."
