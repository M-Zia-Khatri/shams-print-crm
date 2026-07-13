#!/bin/sh
set -e

# ── 1. Validate APP_KEY ─────────────────────────────────────────────────────
if [ -z "${APP_KEY}" ]; then
    echo "ERROR: APP_KEY is not set." >&2
    echo "  Generate one locally with: php artisan key:generate --show" >&2
    echo "  Then add it to .env.production before restarting the containers." >&2
    exit 1
fi

# ── 2. App-only bootstrap (migrations + storage link) ───────────────────────
#    Only run when we are the php-fpm process (i.e. the 'app' service).
#    queue and scheduler containers override CMD, so '$1' will NOT be 'php-fpm'.
if [ "$1" = "php-fpm" ]; then
    echo "INFO: Running database migrations..."
    php artisan migrate --force

    echo "INFO: Ensuring storage symlink exists..."
    if [ ! -L public/storage ]; then
        php artisan storage:link
    else
        echo "INFO: storage symlink already exists, skipping."
    fi
fi

# ── 3. Hand off to the original command ─────────────────────────────────────
exec "$@"
