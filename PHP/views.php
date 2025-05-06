<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

function includePhpFile(Response $response, string $filePath): Response {
    if (is_file($filePath)) {
        ob_start();
        include '/var/views/general/head.php';
        echo '<body>';
        include $filePath;
        echo '</body>';
        $content = ob_get_clean();
        $response->getBody()->write($content);
    } else {
        ob_start();
        include '/var/views/general/head.php';
        echo '<body>';
        include '/var/views/general/404.php';
        echo '</body>';
        $content = ob_get_clean();
        $response->getBody()->write($content);
    }
    return $response->withHeader('Content-Type', 'text/html');
}

return function (App $app) {
    $app->get('/', function (Request $request, Response $response) {    
        $filePath = "/var/views/home.php";
        return includePhpFile($response, $filePath);
    });
};
