FROM php:8.3-apache

# Install dependencies
RUN apt-get update && apt-get install -y libpq-dev libzip-dev zip unzip
RUN docker-php-ext-install pdo pdo_pgsql

# Enable mod_rewrite untuk Laravel
RUN a2enmod rewrite

# Setup working directory
WORKDIR /var/www/html
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Arahkan Document Root ke folder public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf