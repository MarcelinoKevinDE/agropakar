#!/bin/bash
set -e

# Bersihkan cache
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Migrasi saja, jangan jalankan seed di sini jika tidak menggunakan updateOrCreate
php artisan migrate --force

# Jalankan Apache
exec apache2-foreground