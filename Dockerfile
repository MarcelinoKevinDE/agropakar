
# ============================================================================
# Dockerfile — Laravel 13 + PHP 8.3 for Render
# ============================================================================

# ----------------------------------------------------------------------------
# Stage 1 — Composer Dependencies
# ----------------------------------------------------------------------------
FROM composer:2.7 AS vendor

WORKDIR /app

# Copy full project so artisan exists during composer install
COPY . .

# Install production dependencies
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs

# ----------------------------------------------------------------------------
# Stage 2 — Final PHP Image
# ----------------------------------------------------------------------------
FROM php:8.3-fpm-alpine

# ----------------------------------------------------------------------------
# System Packages
# ----------------------------------------------------------------------------
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
    curl \
    bash

# ----------------------------------------------------------------------------
# PHP Extensions
# ----------------------------------------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        gd \
        bcmath \
        pcntl \
        opcache

# Optional Redis Extension
# RUN pecl install redis && docker-php-ext-enable redis

# ----------------------------------------------------------------------------
# Application Directory
# ----------------------------------------------------------------------------
WORKDIR /var/www/html

# ----------------------------------------------------------------------------
# Copy Application Files
# ----------------------------------------------------------------------------
COPY . .

# ----------------------------------------------------------------------------
# Copy Vendor Dependencies From Builder
# ----------------------------------------------------------------------------
COPY --from=vendor /app/vendor ./vendor

# ----------------------------------------------------------------------------
# Remove Cached Files + Old ENV
# ----------------------------------------------------------------------------
RUN rm -rf bootstrap/cache/* \
    && rm -f .env

# ----------------------------------------------------------------------------
# Laravel Writable Directories
# ----------------------------------------------------------------------------
RUN mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# ----------------------------------------------------------------------------
# PHP Opcache Production Settings
# ----------------------------------------------------------------------------
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini

# ----------------------------------------------------------------------------
# Entrypoint
# ----------------------------------------------------------------------------
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# ----------------------------------------------------------------------------
# Expose Port
# ----------------------------------------------------------------------------
EXPOSE 80

# ----------------------------------------------------------------------------
# Start Container
# ----------------------------------------------------------------------------
CMD ["/usr/local/bin/docker-entrypoint.sh"]

