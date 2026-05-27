FROM php:8.3-apache

# Instal dependensi sistem
RUN apt-get update && apt-get install -y libpng-dev libzip-dev unzip git
RUN docker-php-ext-install pdo_mysql gd zip

# Instal Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setup folder
WORKDIR /var/www/html
COPY . .

# Instal dependensi PHP dan build frontend
RUN composer install --no-dev --optimize-autoloader
RUN apt-get install -y nodejs npm && npm install && npm run build

# Konfigurasi Apache agar mengarah ke public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Hak akses
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache