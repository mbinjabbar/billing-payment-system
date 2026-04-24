FROM php:8.2-fpm

# Install system dependencies for gd
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Rest of your Dockerfile...
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-scripts --no-interaction