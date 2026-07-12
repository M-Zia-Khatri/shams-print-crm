# Deployment Guide — Shams Print CRM

This document is the single authoritative reference for deploying the Shams Print CRM production stack from a fresh clone to a live HTTPS endpoint. No commands in this file are executed by the repository itself — all steps are manual and must be run on the VPS by an operator.

---

## Architecture

```
Internet
  └── Host Nginx :443  (SSL termination, crm.ziakhatri.site)
        └── proxy_pass → 127.0.0.1:8080
              └── Docker: nginx  :80  (static assets + FastCGI)
                    └── Docker: app   :9000 (PHP-FPM)

Background workers (same Docker image, no exposed ports):
  ├── Docker: queue      → php artisan queue:work
  └── Docker: scheduler  → php artisan schedule:run (every 60 s)

Data services (internal network only):
  ├── Docker: mysql  (MySQL 8.4)
  └── Docker: redis  (Redis 7.4)
```

The host-level Nginx is **not** managed by this repository. See [`docker/production/HOST_NGINX_SETUP.md`](docker/production/HOST_NGINX_SETUP.md) for those steps.

---

## Prerequisites

- Docker Engine ≥ 26 and Docker Compose V2 (`docker compose`) installed on the VPS.
- Git installed; SSH key or token with read access to the repository.
- A host-level Nginx already running (for other projects or as a fresh install).
- DNS A record for `crm.ziakhatri.site` pointing to the VPS IP (see `HOST_NGINX_SETUP.md`).

---

## Step 1 — Clone the Repository

```bash
git clone <repo-url> /srv/shams-print-crm
cd /srv/shams-print-crm
```

---

## Step 2 — Create the Production Environment File

```bash
cp .env.production.example .env.production
nano .env.production   # or your preferred editor
```

Set **at minimum** the following values before continuing:

| Variable | Description |
|---|---|
| `APP_KEY` | Generate with `php artisan key:generate --show` (or use any base64:32-byte string) |
| `APP_URL` | `https://crm.ziakhatri.site` |
| `APP_PORT` | `8080` (must not conflict with `80`, `3000`, or `5000` on this VPS) |
| `DB_PASSWORD` | Strong password for the `shams` MySQL user |
| `DB_ROOT_PASSWORD` | Strong MySQL root password |
| `REDIS_PASSWORD` | Strong Redis password |
| `CLOUDINARY_*` | Your Cloudinary credentials |

> [!IMPORTANT]
> `APP_PORT` defaults to `8080` in `.env.production.example`. Do not change it to `80`, `3000`, or `5000` — those ports are occupied by other services on this VPS.

---

## Step 3 — Build the Production Image

```bash
docker compose -f docker/production/docker-compose.yml build
```

This builds the PHP-FPM image (`shams-print-crm-php:production`) once. The `queue` and `scheduler` services reuse the same image.

---

## Step 4 — Start All Containers

```bash
docker compose -f docker/production/docker-compose.yml up -d
```

The following containers will start:

| Container | Role |
|---|---|
| `app` | PHP-FPM application server |
| `nginx` | Public HTTP entrypoint (host port `APP_PORT`) |
| `queue` | Background job worker (`php artisan queue:work`) |
| `scheduler` | Cron-style task runner (`schedule:run` every 60 s) |
| `mysql` | MySQL 8.4 database |
| `redis` | Redis 7.4 (cache, sessions, queue broker) |

---

## Step 5 — Run Post-Deploy Artisan Commands

```bash
# Run database migrations
docker compose -f docker/production/docker-compose.yml \
    exec app php artisan migrate --force

# Cache config, routes, and views for production performance
docker compose -f docker/production/docker-compose.yml \
    exec app php artisan optimize

# Create the public storage symlink (run once)
docker compose -f docker/production/docker-compose.yml \
    exec app php artisan storage:link
```

> [!NOTE]
> `migrate --force` is required because Laravel refuses to run migrations in production without this flag. Review pending migrations before applying on a live database.

---

## Step 6 — Configure Host Nginx and SSL

Follow **all steps** in [`docker/production/HOST_NGINX_SETUP.md`](docker/production/HOST_NGINX_SETUP.md) to:
1. Create the host Nginx server block proxying `crm.ziakhatri.site` → `127.0.0.1:8080`.
2. Enable the site and reload Nginx.
3. Obtain an SSL certificate with `certbot --nginx -d crm.ziakhatri.site`.

---

## Checking Queue and Scheduler Logs

The `queue` and `scheduler` workers run as separate long-lived containers. Monitor them with:

```bash
# Follow queue worker output
docker compose -f docker/production/docker-compose.yml logs -f queue

# Follow scheduler output
docker compose -f docker/production/docker-compose.yml logs -f scheduler

# Follow all containers at once
docker compose -f docker/production/docker-compose.yml logs -f
```

If a job fails, it will appear in the queue worker logs. Failed jobs can be retried via:

```bash
docker compose -f docker/production/docker-compose.yml \
    exec app php artisan queue:retry all
```

---

## Coexisting with Another Project on This VPS

This stack is specifically configured for shared-VPS deployment. Key design decisions:

- **`APP_PORT=8080`** — The Docker Nginx binds only to `127.0.0.1:8080` on the host, avoiding the existing project's port `80`, `3000`, and `5000` bindings.
- **`internal: true` removed** — The `shams_internal` Docker network uses a plain bridge driver so containers can reach external services (Cloudinary API, mail servers, etc.) while remaining isolated from each other on the host.
- **No host-level port forwarding for MySQL or Redis** — Database and cache services are only reachable within the Docker network; they expose no host ports.
- The host-level Nginx manages SSL and routes `crm.ziakhatri.site` traffic to the Docker stack, completely independent of the other project's Nginx configuration.

See [`docker/production/HOST_NGINX_SETUP.md`](docker/production/HOST_NGINX_SETUP.md) for the full host Nginx and SSL setup.

---

## Day-2 Operations

### Update the Application

```bash
git pull
docker compose -f docker/production/docker-compose.yml build
docker compose -f docker/production/docker-compose.yml up -d
docker compose -f docker/production/docker-compose.yml \
    exec app php artisan migrate --force
docker compose -f docker/production/docker-compose.yml \
    exec app php artisan optimize
```

### Stop All Containers

```bash
docker compose -f docker/production/docker-compose.yml down
```

### Open a Shell in the App Container

```bash
docker compose -f docker/production/docker-compose.yml exec app sh
```

### Clear and Rebuild Laravel Caches

```bash
docker compose -f docker/production/docker-compose.yml \
    exec app php artisan optimize:clear
docker compose -f docker/production/docker-compose.yml \
    exec app php artisan optimize
```

---

## Troubleshooting

### Port conflict — `APP_PORT` already in use

If `docker compose up` fails with an address-in-use error:

1. Find what is using the port: `sudo lsof -i :8080` (or `ss -tlnp | grep 8080`).
2. Either stop that process or choose a different `APP_PORT` value in `.env.production` (e.g. `8081`).
3. Update the host Nginx `proxy_pass` in `/etc/nginx/sites-available/crm.ziakhatri.site` to match.
4. Reload Nginx: `sudo systemctl reload nginx`.
5. Restart the stack: `docker compose -f docker/production/docker-compose.yml up -d`.

> [!CAUTION]
> Never set `APP_PORT` to `80`, `3000`, or `5000` — these are occupied by the coexisting project on this VPS.

### Database or Redis health checks fail on startup

```bash
docker compose -f docker/production/docker-compose.yml logs mysql
docker compose -f docker/production/docker-compose.yml logs redis
```

Verify `DB_PASSWORD`, `DB_ROOT_PASSWORD`, and `REDIS_PASSWORD` in `.env.production` match the values the containers were first started with. If you changed them, you may need to remove the named volumes and recreate:

```bash
docker compose -f docker/production/docker-compose.yml down -v
docker compose -f docker/production/docker-compose.yml up -d
```

> [!WARNING]
> `down -v` **deletes all volume data including the database**. Only do this on a fresh install or after taking a backup.

### Queue jobs are not processing

```bash
docker compose -f docker/production/docker-compose.yml ps queue
docker compose -f docker/production/docker-compose.yml logs queue
```

If the container is not running, restart it:

```bash
docker compose -f docker/production/docker-compose.yml up -d queue
```

### Scheduled tasks are not firing

```bash
docker compose -f docker/production/docker-compose.yml ps scheduler
docker compose -f docker/production/docker-compose.yml logs scheduler
```

The scheduler loop runs `php artisan schedule:run` every 60 seconds inside the container. Confirm `APP_ENV=production` is set correctly in `.env.production`.
