FROM php:8.2-fpm

# Install gd extension
RUN docker-php-ext-install gd

# Then install composer dependencies
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-scripts --no-interaction