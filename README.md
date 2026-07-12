# Shams Print CRM Infrastructure

Production-ready infrastructure and DevOps tooling for a fresh Laravel Blade application. This repository intentionally contains only framework defaults plus Docker, Nginx, environment, and Makefile setup; no application-specific routes, controllers, models, migrations, seeders, factories, Blade pages, authentication, or API scaffolding are added here.

## Stack

- Laravel 13 / PHP 8.5
- Blade with Vite and Tailwind from the default Laravel frontend toolchain
- MySQL 8.4
- Redis 7.4
- Docker Compose
- Nginx + PHP-FPM for production
- Makefile workflow helpers

## Directory layout

```text
docker/
  development/
    docker-compose.yml      # MySQL + Redis only; Laravel runs on the host
  production/
    docker-compose.yml      # PHP-FPM + Nginx + MySQL + Redis
    nginx/default.conf      # Laravel Nginx virtual host
    php/Dockerfile          # Production PHP-FPM image
Makefile                    # Common local and production commands
.env.example                # Local infrastructure defaults
.env.production.example     # Production infrastructure defaults
```

## Local development workflow

The development Docker environment runs only MySQL and Redis. Run Laravel and Vite on the host so local PHP tooling, Vite hot reload, and editor integrations work normally.

### 1. Install dependencies

```bash
make install
```

This installs Composer and NPM dependencies, creates `.env` from `.env.example` when missing, and generates `APP_KEY`.

### 2. Start infrastructure services

```bash
make up
```

This starts:

- MySQL on `127.0.0.1:${FORWARD_DB_PORT:-3306}`
- Redis on `127.0.0.1:${FORWARD_REDIS_PORT:-6379}`

### 3. Run Laravel and Vite on the host

In separate terminals:

```bash
php artisan serve
npm run dev
```

Or use the existing Composer script if desired:

```bash
composer run dev
```

### 4. Run framework commands

```bash
make migrate
make test
make clear
```

## Production Docker workflow

The production Docker environment is fully containerized and includes:

- `app`: PHP-FPM application container
- `nginx`: public HTTP entrypoint serving Laravel from `public/`
- `queue`: background job worker (`php artisan queue:work`)
- `scheduler`: cron-style task runner (`php artisan schedule:run` every 60 s)
- `mysql`: persistent MySQL database
- `redis`: persistent Redis instance for cache, sessions, and queues

> **Sharing a VPS?** Set `APP_PORT` to a value that does not conflict with ports already in use on the host — specifically, avoid `80`, `3000`, and `5000`. The default is `8080`. For a complete deployment walkthrough and host-level Nginx/SSL setup instructions, see:
> - [DEPLOYMENT.md](DEPLOYMENT.md) — end-to-end deployment guide
> - [docker/production/HOST_NGINX_SETUP.md](docker/production/HOST_NGINX_SETUP.md) — host Nginx reverse proxy and Certbot steps

### 1. Create production environment file

```bash
cp .env.production.example .env.production
```

Set production values before deploying:

- `APP_KEY` — generate a secure Laravel key
- `APP_URL` — public application URL
- `DB_PASSWORD` — strong database user password
- `DB_ROOT_PASSWORD` — strong MySQL root password
- `REDIS_PASSWORD` — strong Redis password
- `APP_PORT` — host port for Nginx, defaults to `80`

### 2. Build and start production containers

```bash
make prod-up
```

### 3. Run production commands in the app container

```bash
make prod-shell
php artisan migrate --force
php artisan optimize
```

### 4. Stop production containers

```bash
make prod-down
```

## Docker details

### Development Compose

`docker/development/docker-compose.yml` runs only MySQL and Redis. It uses named volumes for persistence and health checks for both services.

### Production Compose

`docker/production/docker-compose.yml` runs PHP-FPM, Nginx, MySQL, and Redis on a dedicated Docker network. MySQL, Redis, Laravel storage, and Laravel cache directories use named volumes for persistence across container rebuilds.

### Nginx

`docker/production/nginx/default.conf` is configured for Laravel with:

- `public/` as the document root
- `try_files` fallback to `index.php`
- PHP-FPM upstream at `app:9000`
- standard FastCGI parameters including `SCRIPT_FILENAME`
- security headers
- hidden dotfile denial except `.well-known`
- gzip compression
- long-lived static asset caching
- `/healthz` endpoint for container health checks

## Environment configuration

### MySQL

Local `.env.example` uses MySQL by default:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shams_print_crm
DB_USERNAME=shams
DB_PASSWORD=secret
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

Production points Laravel at the Compose service name:

```dotenv
DB_HOST=mysql
```

Both Docker environments set MySQL server charset to `utf8mb4` and collation to `utf8mb4_unicode_ci`.

### Redis

Laravel is wired to Redis for cache, sessions, and queues through environment variables:

```dotenv
CACHE_STORE=redis
SESSION_DRIVER=redis
SESSION_CONNECTION=default
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_DB=0
REDIS_CACHE_DB=1
```

Production uses the Redis service name and requires `REDIS_PASSWORD`:

```dotenv
REDIS_HOST=redis
REDIS_PASSWORD=change-redis-me
```

The production PHP image installs and enables the `redis` PHP extension.

## Makefile commands

| Command | Description |
| --- | --- |
| `make install` | Install Composer/NPM dependencies, create `.env`, generate app key |
| `make up` | Start development MySQL and Redis |
| `make down` | Stop development MySQL and Redis |
| `make restart` | Restart development services |
| `make logs` | Follow development service logs |
| `make shell` | Open a host shell in the project directory |
| `make migrate` | Run Laravel migrations on the configured database |
| `make fresh` | Run `migrate:fresh` on the configured database |
| `make cache` | Cache Laravel config, routes, and views |
| `make optimize` | Run Laravel optimize |
| `make clear` | Clear Laravel optimized caches |
| `make test` | Run the default Laravel test suite |
| `make lint` | Format dirty PHP files with Laravel Pint |
| `make format` | Format dirty PHP files with Laravel Pint |
| `make prod-up` | Build and start production containers |
| `make prod-down` | Stop production containers |
| `make prod-logs` | Follow production service logs |
| `make prod-shell` | Open a shell in the production PHP-FPM container |

## Troubleshooting

### `vendor/autoload.php` is missing

Run:

```bash
composer install
```

Or run the full setup:

```bash
make install
```

### MySQL port is already in use

Change the forwarded development port in `.env`:

```dotenv
FORWARD_DB_PORT=3307
```

Then restart services:

```bash
make restart
```

### Redis port is already in use

Change the forwarded development port in `.env`:

```dotenv
FORWARD_REDIS_PORT=6380
```

Then update `REDIS_PORT` if Laravel should connect to the forwarded host port.

### Frontend changes are not visible

Run Vite in development:

```bash
npm run dev
```

For production assets, build them before creating the production image or during deployment:

```bash
npm run build
```

### Production app returns a Laravel cache/config issue

Clear and rebuild Laravel caches inside the app container:

```bash
make prod-shell
php artisan optimize:clear
php artisan optimize
```

### Database or Redis health checks fail

Inspect container logs:

```bash
make logs
make prod-logs
```

Verify `.env` or `.env.production` values match the Docker Compose service credentials.
