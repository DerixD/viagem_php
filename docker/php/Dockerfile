
FROM php:8.3-fpm

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    gdal-bin \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Verify GDAL PostgreSQL driver
RUN ogrinfo --formats | grep PostgreSQL

WORKDIR /var/www/html

# Composer autoload
ENV COMPOSER_ALLOW_SUPERUSER=1
