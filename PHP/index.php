<?php
require __DIR__ . '/../vendor/autoload.php';
require_once '/var/php/API/middleware.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use DI\Container;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use API\AuthMiddleware;
use SHARED\AuthorizationLevel;
use SHARED\AuthLevelRoute;
use SHARED\Method;
/**
 * ==========================================================
 *                    PDO para MySQL
 * ==========================================================
 */
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
/**
 * ==========================================================
 *                 Proteccion de rutas
 * ==========================================================
 */
$routes = [
    new AuthLevelRoute('/private/.*', [Method::ALL], [AuthorizationLevel::ADMINISTRADOR]),
    new AuthLevelRoute('/protected/mis_articulos.*', [Method::ALL], [AuthorizationLevel::CONTACTO, AuthorizationLevel::REVISOR]),
    new AuthLevelRoute('/protected/publicar', [Method::ALL], [AuthorizationLevel::CONTACTO, AuthorizationLevel::REVISOR]),
    new AuthLevelRoute('/protected/mis_revisiones.*', [Method::ALL], [AuthorizationLevel::REVISOR]),
    new AuthLevelRoute('/protected/revisar.*', [Method::ALL], [AuthorizationLevel::REVISOR]),
    new AuthLevelRoute('/protected/revisar.*', [Method::ALL], [AuthorizationLevel::REVISOR]),
    new AuthLevelRoute('/protected/eliminar_cuenta', [Method::ALL], [AuthorizationLevel::REVISOR, AuthorizationLevel::CONTACTO]),
];
/**
 * ==========================================================
 *                Inicializacion de App
 * ==========================================================
 */
$twig = Twig::create('/var/views', ['cache' => false]);
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->add(new AuthMiddleware($routes));
$app->add(TwigMiddleware::create($app, $twig));
$app->addBodyParsingMiddleware();

$routes = require '/var/php/routes.php';
$routes($app, $twig);

$app->run();