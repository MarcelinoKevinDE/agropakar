#!/bin/sh

set -e

echo "Laravel startup..."

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

php artisan migrate --force || true

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Apache..."

exec apache2-foreground