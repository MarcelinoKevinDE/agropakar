# Dockerfile — Laravel 13 + PHP 8.3 for Render
# ============================================================================
#
# Use this if you set `runtime: docker` in render.yaml.
# Gives you full control over the PHP environment and cache invalidation.
#
# ============================================================================

# ---- Stage 1: Composer dependencies ----------------------------------------
FROM composer:2.7 AS vendor

WORKDIR /app

# Copy only composer files first — better Docker layer caching
COPY composer.json composer.lock ./

# Install production dependencies
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs

# ---- Stage 2: Node/Vite build (skip if no frontend build step) -------------
# FROM node:20-alpine AS frontend
# WORKDIR /app
# COPY package*.json vite.config.js ./
# COPY resources/ resources/
# RUN npm ci && npm run build

# ---- Stage 3: Final PHP image -----------------------------------------------
FROM php:8.3-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-client \
    libpq-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    curl

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        gd \
        bcmath \
        pcntl \
        opcache

# Install Redis extension (optional)
# RUN pecl install redis && docker-php-ext-enable redis

WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy vendor from Stage 1
COPY --from=vendor /app/vendor ./vendor

# Copy frontend assets from Stage 2 (uncomment if using)
# COPY --from=frontend /app/public/build ./public/build

# ── CRITICAL: Remove any stale cached config from the build context ──────────
# This prevents ghost cache entries (the 'spgsql' / 'forge' problem).
RUN rm -f \
    bootstrap/cache/config.php \
    bootstrap/cache/routes-v7.php \
    bootstrap/cache/events.php \
    bootstrap/cache/packages.php \
    bootstrap/cache/services.php \
    .env

# Storage and cache dirs — these must exist and be writable
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# PHP OPcache configuration for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Supervisor configuration (manages nginx + php-fpm)
COPY docker/supervisord.conf /etc/supervisord.conf

# Startup script — generates config cache at runtime (after env vars are injected)
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]