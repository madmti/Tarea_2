FROM php:8.2-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y unzip libzip-dev \
    && docker-php-ext-install zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY ./composer.json ./

RUN mkdir -p public && \
    cat > public/index.php <<'EOF'
<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

$app = AppFactory::create();

$front_routes = require '/var/php/views.php';
$front_routes($app);

$back_routes = require '/var/php/endpoints.php';
$app->group('/api', function (RouteCollectorProxy $group) use ($back_routes) {
    $back_routes($group);
});

$app->run();
EOF

RUN composer install
