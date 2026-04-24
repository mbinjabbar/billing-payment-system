FROM php:8.3-fpm

WORKDIR /app

# Install system dependencies for gd and zip
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# Copy application code
COPY . .

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]