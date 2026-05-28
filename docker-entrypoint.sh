#!/bin/bash
set -e

# Bersihkan cache sebelum start
php artisan config:clear
php artisan cache:clear

# Jalankan migrasi database
php artisan migrate --force

# Jalankan Apache
exec apache2-foreground