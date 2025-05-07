FROM php:8.2-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y unzip libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY ./composer.json ./

RUN composer install
