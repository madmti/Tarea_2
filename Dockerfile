FROM php:8.2-apache

# Dependencias del sistema y PHP
RUN apt-get update && apt-get install -y \
    unzip zip curl git libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Habilita Apache rewrite
RUN a2enmod rewrite

# Directorio de trabajo en contenedor
WORKDIR /var/www/html
