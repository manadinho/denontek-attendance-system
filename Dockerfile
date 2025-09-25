FROM php:8.2-fpm

# System deps
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev libzip-dev zip libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Composer cache optimization
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/var/www/html/vendor/bin"

# If you want to build vendor in image (optional for dev):
# COPY composer.json composer.lock ./
# RUN composer install --no-scripts --no-interaction --prefer-dist

# Permissions for storage/bootstrap cache at runtime
RUN usermod -u 1000 www-data || true \
    && groupmod -g 1000 www-data || true
