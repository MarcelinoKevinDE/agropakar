#!/bin/bash
set -e

echo "==> Clearing config cache (to pick up runtime env vars)..."
php artisan config:clear

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Re-caching config..."
php artisan config:cache

echo "==> Starting Apache..."
exec apache2-foreground