FROM php:8.2-apache

# Instalace závislostí a rozšíření pro MySQL a SQLite
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite zip

# Povolení modulu rewrite pro Apache
RUN a2enmod rewrite

# Přesměrování výchozího kořene Apache (DocumentRoot) ze /var/www/html na /var/www/html/public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Stažení Composera
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Zkopírování souborů závislostí a automatická instalace knihoven při buildu
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts