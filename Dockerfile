FROM php:8.3-apache

# 1. Instal dependensi sistem yang diperlukan
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libzip-dev \
    unzip \
    git \
    libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql gd zip

# 2. Instal Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Setup direktori kerja
WORKDIR /var/www/html
COPY . .

# 4. Instal dependensi PHP dan Node.js
RUN composer install --no-dev --optimize-autoloader
RUN apt-get install -y nodejs npm && npm install && npm run build

# 5. Konfigurasi Apache (Arahkan ke folder public)
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# 6. Set izin akses folder agar Laravel bisa menulis cache & log
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Jalankan Migrasi & Cache sebelum Apache mulai
# PENTING: Pastikan database sudah terhubung di Environment Variables Render
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# 8. Command utama
CMD bash -c "php artisan migrate --force && apache2-foreground"
CMD bash -c "php artisan migrate:fresh --force && apache2-foreground"