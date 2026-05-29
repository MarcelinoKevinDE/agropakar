#!/bin/sh

set -e

echo "Clearing Laravel caches..."

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

echo "Running migrations..."

php artisan migrate --force || true

echo "Caching config..."

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Apache..."

exec apache2-foreground