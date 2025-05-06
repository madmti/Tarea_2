<?php
require_once __DIR__ . '/API/responses.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;
use API\ResponseHelper;

return function (RouteCollectorProxy $app, Twig $twig) {
    $app->get('/', function (Request $request, Response $response) use ($twig) {
        $data = ['message' => 'Hello from API!'];
        return ResponseHelper::json($response, $data);
    });
};
