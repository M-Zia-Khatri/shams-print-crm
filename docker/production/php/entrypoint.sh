#!/bin/sh
set -e

# ── 1. Validate APP_KEY ─────────────────────────────────────────────────────
if [ -z "${APP_KEY}" ]; then
    echo "ERROR: APP_KEY is not set." >&2
    echo "  Generate one locally with: php artisan key:generate --show" >&2
    echo "  Then add it to .env.production before restarting the containers." >&2
    exit 1
fi

# ── 2. Seed the shared public/ volume (app-only) ───────────────────────────
#    The `app_public` named volume mounts over ./public in every container
#    that uses it, hiding the baked-in index.php/build/ on first boot (empty
#    volume). Seed it from the pristine snapshot taken at image build time.
#    Only the 'app' (php-fpm) service performs the seed to avoid races with
#    queue/scheduler starting concurrently.
if [ "$1" = "php-fpm" ]; then
    if [ ! -f /var/www/html/public/index.php ]; then
        echo "INFO: Seeding empty public/ volume from image snapshot..."
        cp -a /var/www/html/public-image/. /var/www/html/public/
    fi
fi

# ── 2b. Fix ownership on shared named volumes (root-owned on first boot) ──
#    Runs on every service (app/queue/scheduler) since all three mount
#    app_storage/app_cache; only 'app' also mounts app_public.
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
if [ "$1" = "php-fpm" ]; then
    chown -R www-data:www-data /var/www/html/public
fi

# ── 3. App-only bootstrap (migrations) ─────────────────────────────────────
#    Only run when we are the php-fpm process (i.e. the 'app' service).
#    queue and scheduler containers override CMD, so '$1' will NOT be 'php-fpm'.
if [ "$1" = "php-fpm" ]; then
    echo "INFO: Running database migrations..."
    php artisan migrate --force
fi

# ── 4. Hand off to the original command ─────────────────────────────────────
exec "$@"