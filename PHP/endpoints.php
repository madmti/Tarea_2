<?php
require_once '/var/www/html/vendor/autoload.php';
require_once '/var/php/API/functions.php';
require_once '/var/php/API/responses.php';
require_once '/var/php/API/jwt.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Slim\App;
use API\Functions;
use API\ResponseHelper;
use API\JwtHelper;
/*
USAR Functions::funcion para llamar a procedimientos de la BD
USAR ResponseHelper::respuesta al final
*/
return function (App $app, Twig $twig) {
/**
 * ===================================================================================================
 *                                      CUENTAS DE USUARIO
 * ===================================================================================================
 */
    $app->post('/registro', function ($request, $response) {

    });

    $app->get('/logout', function (Request $request, Response $response) {

    });

    $app->post('/protected/eliminar_cuenta', function (Request $request, Response $response) {

});

    $app->post('/login', function (Request $request, Response $response) {
    
    });

    $app->post('/editar_cuenta', function (Request $request, Response $response) {

    });

/**
 * ===================================================================================================
 *                                        ARTICULOS
 * ===================================================================================================
 */
    $app->post('/protected/publicar', function ($request, $response) {

    });

    $app->post('/protected/revisar/{id_articulo}', function ($request, $response, $args) {

    });
    
    $app->post('/protected/mis_articulos/{id}/editar', function ($request, $response, $args) {

    });

    $app->get('/protected/mis_articulos/{id}/eliminar', function ($request, $response, $args) {

    });

/**
 * ===================================================================================================
 *                                        ADM -> REVISORES
 * ===================================================================================================
 */
    $app->post('/private/revisores/nuevo', function ($request, $response) {

    });

    $app->post('/private/revisores/editar/{id}', function ($request, $response, $args) {

    });

    $app->post('/private/revisores/quitar/{id}', function ($request, $response, $args) {

    });
/**
 * ===================================================================================================
 *                                        ADM -> ASIGNACION
 * ===================================================================================================
 */
    $app->post('/private/quitar_art/{id_articulo}/revisor/{id_revisor}/{section}', function ($request, $response, $args) {

    });

    $app->post('/private/asignar_art/{id_articulo}/revisor/{id_revisor}/{section}', function ($request, $response, $args) {

    });
};