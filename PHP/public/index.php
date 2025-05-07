<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use DI\Container;

$container = new Container();
$container->set('db', function () {
    $host     = getenv('MYSQL_HOST') ?: 'localhost';
    $port     = getenv('MYSQL_PORT') ?: '3306';
    $dbname   = getenv('MYSQL_DATABASE');
    $user     = getenv('MYSQL_USER');
    $pass     = getenv('MYSQL_PASSWORD');
    $charset  = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
});

AppFactory::setContainer($container);
$app = AppFactory::create();

$front_routes = require '/var/php/views.php';
$front_routes($app);

$back_routes = require '/var/php/endpoints.php';
$app->group('/api', function (RouteCollectorProxy $group) use ($back_routes) {
    $back_routes($group);
});

$app->run();