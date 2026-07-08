# MICS HUB Docker Deployment Guide

## Purpose

This Docker path is for immutable production-style releases. It is separate from DDEV, which remains the preferred local development environment.

## Architecture

- `app`: one immutable `ghcr.io/iwakur/mics:1.0.0` image containing Laravel, PHP-FPM 8.4, Caddy, Composer dependencies, and built frontend assets. Supervisor keeps PHP-FPM and Caddy running inside the container.
- `db`: PostgreSQL 17 as the stateful service.

This split keeps the release artifact deterministic:

- no `composer install` at runtime;
- no `npm install` or `vite build` at runtime;
- no source-code bind mounts in production;
- database migration remains a separate controlled deployment step.

## Files

- `Dockerfile`: multi-stage build for the single production application image.
- `.dockerignore`: keeps local state and non-runtime files out of the build context.
- `compose.prod.yml`: exact-image orchestration for PostgreSQL, migrations, and application runtime.
- `.env.docker.example`: container environment template.
- `docker/entrypoint.sh`: runtime safety checks and optional Laravel optimization.
- `docker/php/php.ini`: production-leaning PHP and opcache settings.
- `docker/caddy/Caddyfile`: static-file serving and PHP proxy configuration.
- `docker/supervisor/supervisord.conf`: process definitions for PHP-FPM and Caddy.
- `.github/workflows/release.yml`: tests pull requests and pushes to `main`, and publishes exact Git-tag releases to GHCR only after the same gate passes.

## Build and Run

1. Copy the Docker environment template and set a real app key and database password.

```bash
cp .env.docker.example .env.docker
php artisan key:generate --show
```

2. Put the generated key into `.env.docker` as `APP_KEY=base64:...`.

3. Build the exact image locally when needed.

```bash
docker build -t ghcr.io/iwakur/mics:1.0.0 .
```

4. Start PostgreSQL first.

```bash
docker compose --env-file .env.docker -f compose.prod.yml up -d db
```

5. Run migrations once for the release.

```bash
docker compose --env-file .env.docker -f compose.prod.yml --profile tools run --rm migrate
```

6. On the first deployment only, create the initial linked administrator interactively:

```bash
docker compose --env-file .env.docker -f compose.prod.yml run --rm app php artisan app:bootstrap-administrator
```

Run this after migrations and before exposing the application publicly. The command refuses to create another account once an active administrator exists.

7. Start the application container.

```bash
docker compose --env-file .env.docker -f compose.prod.yml up -d app
```

The app will be reachable at `http://localhost:8080` unless `APP_PORT` is changed.

## Release Discipline

Treat the Git tag and matching exact-version image as the release artifacts. The intended flow is:

1. push or merge the release commit to `main`, where CI runs `composer check` without publishing an image;
2. create an annotated semantic-version Git tag on that tested commit, such as `v1.0.0`;
3. push the tag, causing CI to rerun `composer check` against the tagged commit;
4. build and push `ghcr.io/iwakur/mics:1.0.0` only after the tag checks pass;
5. pull that exact version on the server;
6. update `compose.prod.yml` to that exact version;
7. run `php artisan migrate --force` once;
8. start or replace the application container.

The workflow removes the leading `v` only for the Docker tag. CI refuses to overwrite an existing registry version, rejects tags that are not exactly `vMAJOR.MINOR.PATCH`, and deliberately publishes no `latest` tag.

For the first release, after committing and pushing the completed work:

```bash
git tag -a v1.0.0 -m "MICS HUB 1.0.0"
git push origin v1.0.0
```

This is atomic at the application-image level. Schema changes remain an explicit step because Laravel cannot safely infer whether a migration is backward-compatible with already-running containers.

## Runtime Notes

- `APP_RUN_OPTIMIZE=true` runs `php artisan optimize` during startup. Keep it enabled in normal production use.
- `LOG_CHANNEL=stderr` sends logs to the container log stream instead of local files.
- Sessions, cache, and queue remain database-backed to match the current application design. Redis is unnecessary until the application actually adopts Redis-backed workflows.
- Preserve `APP_KEY` as a backed-up deployment secret. Changing or losing it invalidates encrypted cookies and makes encrypted payout-card values unreadable.
- Application source is root-owned and read-only to the runtime user. Only `storage/` and `bootstrap/cache/` are writable by PHP-FPM.

## VPS Update

Authenticate once with a GitHub personal access token that can read packages, then deploy the version explicitly:

```bash
echo "$GHCR_TOKEN" | docker login ghcr.io -u YOUR_GITHUB_USERNAME --password-stdin
docker compose --env-file .env.docker -f compose.prod.yml pull app migrate
docker compose --env-file .env.docker -f compose.prod.yml up -d db
docker compose --env-file .env.docker -f compose.prod.yml --profile tools run --rm migrate
docker compose --env-file .env.docker -f compose.prod.yml run --rm app php artisan app:bootstrap-administrator # first deploy only
docker compose --env-file .env.docker -f compose.prod.yml up -d app
```

Change the two image references in `compose.prod.yml` only when intentionally moving to another published version.
