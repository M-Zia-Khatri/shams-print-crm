# =============================================================================
# Stage 1: Node.js — build Vite/CSS/JS frontend assets
# =============================================================================
FROM node:22-alpine AS node_builder

WORKDIR /app

# Copy dependency manifests first to leverage Docker layer caching.
# Subsequent source changes won't re-run npm install unless these files change.
COPY package.json package-lock.json ./

# Install ALL dev dependencies (Vite, Tailwind, etc.) needed for the build.
RUN npm ci --include=dev

# Copy the rest of the source so Vite can resolve inputs.
COPY . .

# Compile frontend assets into public/build/
RUN npm run build

# =============================================================================
# Stage 2: PHP — production application image
# =============================================================================
FROM php:8.5-fpm-alpine AS production

WORKDIR /var/www/html

# ---------------------------------------------------------------------------
# System dependencies + PHP extensions
# ---------------------------------------------------------------------------
RUN apk add --no-cache \
        bash \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        mysql-client \
        oniguruma-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        zip \
    && (php -m | grep -qi opcache || docker-php-ext-install opcache) \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# ---------------------------------------------------------------------------
# Composer — install PHP dependencies (layer-cache friendly)
# ---------------------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy dependency manifests before the full source so that the install layer
# is only invalidated when composer.json / composer.lock actually change.
COPY composer.json composer.lock ./

RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts

# ---------------------------------------------------------------------------
# Application source
# ---------------------------------------------------------------------------
COPY . .

# Re-generate optimised autoloader now that the full source is present.
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# ---------------------------------------------------------------------------
# Vite build output (from node_builder stage)
# No node_modules are copied — only the compiled assets.
# ---------------------------------------------------------------------------
COPY --from=node_builder /app/public/build ./public/build

# ---------------------------------------------------------------------------
# Permissions, storage directories, and storage symlink
# ---------------------------------------------------------------------------
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

RUN php artisan storage:link

# ---------------------------------------------------------------------------
# Entrypoint
# ---------------------------------------------------------------------------
COPY docker/production/php/entrypoint.sh /usr/local/bin/entrypoint.sh

USER root
RUN chmod +x /usr/local/bin/entrypoint.sh
USER www-data

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
