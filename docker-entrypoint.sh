```bash
#!/bin/sh

set -e

cd /var/www/html

echo "Running Laravel setup..."

# Clear old cache
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

# Cache config for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run migrations
php artisan migrate --force || true

echo "Starting PHP-FPM..."

exec php-fpm -F
```
