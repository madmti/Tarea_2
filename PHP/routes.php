<?php
require_once '/var/www/html/vendor/autoload.php';
require_once '/var/php/API/functions.php';
require_once '/var/php/API/responses.php';
require_once '/var/php/API/jwt.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Slim\App;

return function (App $app, Twig $twig) {
    $front_routes = require '/var/php/views.php';
    $back_routes = require '/var/php/endpoints.php';

    $front_routes($app, $twig);
    $back_routes($app, $twig);
};
