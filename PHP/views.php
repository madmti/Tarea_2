<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Views\Twig;

return function (App $app, Twig $twig) {
    $app->get('/', function (Request $request, Response $response) use ($twig) {
        return $twig->render($response, 'saludo.html', ['nombre' => 'Mundo']);
    });

    $app->get('/saludo/{nombre}', function (Request $request, Response $response, $args) use ($twig) {
        return $twig->render($response, 'saludo.html', ['nombre' => $args['nombre']]);
    });
};
