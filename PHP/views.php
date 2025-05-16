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

return function (App $app, Twig $twig) {
/**
 * ===================================================================================================
 *                                          GENERAL
 * ===================================================================================================
 */
    $app->get('/', function (Request $request, Response $response) use ($twig) {

        return $twig->render($response, 'home.twig', [
            'user' => [],
            'total' => 0,
            'articulos' => [],
            'categorias' => [],
            'queryParams' => [],
            'revisores' => [],
            'error' => null,
            'info' => null,
        ]);
    });

    $app->get('/registro', function (Request $request, Response $response) use ($twig) {

        return $twig->render($response, 'registro.twig', [
            'on_log_reg' => true,
            'error' => null,
            'info' => null,
        ]);
    });

    $app->get('/login', function (Request $request, Response $response) use ($twig) {

        return $twig->render($response, 'login.twig', [
            'on_log_reg' => true,
            'error' => null,
            'info' => null,
        ]);
    });

    $app->get('/mi_cuenta', function (Request $request, Response $response) use ($twig) {

        return $twig->render($response, 'mi_cuenta.twig', [
            'user' => [],
            'info' => null,
            'error' => null,
        ]);
    });

    $app->get('/editar_cuenta', function (Request $request, Response $response) use ($twig) {

        return $twig->render($response, 'editar.twig', [
            'user' => [],
            'error' => null,
            'info' => null,
        ]);
    });

    $app->get('/articulo/{id}', function (Request $request, Response $response, $args) use ($twig) {

        return $twig->render($response, 'articulo.twig', [
            'user' => [],
            'articulo' => [],
            'error' => null,
            'info' => null,
        ]);
    });
/**
 * ===================================================================================================
 *                                     PROPIETARIOS (AUT/REV)
 * ===================================================================================================
 */
    $app->get('/protected/mis_articulos', function (Request $request, Response $response) use ($twig) {
        
        return $twig->render($response, 'autor/mis_articulos.twig', [
            'user' => [],
            'total' => 0,
            'articulos' => [],
            'info' => null,
            'error' => null,
        ]);
    });

    $app->get('/protected/publicar', function (Request $request, Response $response) use ($twig) {
        
        return $twig->render($response, 'autor/publicar.twig', [
            'user' => [],
            'topicos' => [],
            'autores' => [],
            'error' => null,
            'info' => null,
        ]);
    });

    $app->get('/protected/mis_articulos/{id}', function (Request $request, Response $response, $args) use ($twig) {

        return $twig->render($response, 'autor/detalles.twig', [
            'user' => [],
            'articulo' => [],
            'detalles' => [],
            'info' => null,
            'error' => null,
        ]);
    });

    $app->get('/protected/mis_articulos/{id}/editar', function (Request $request, Response $response, $args) use ($twig) {

        return $twig->render($response, 'autor/editar_articulo.twig', [
            'user' => [],
            'articulo' => [],
            'topicos' => [],
            'topicos_asociados' => [],
            'autores' => [],
            'autores_asociados' => [],
            'error' => null,
            'info' => null,
        ]);
    });
/**
 * ===================================================================================================
 *                                        REVISORES
 * ===================================================================================================
 */
    $app->get('/protected/mis_revisiones', function (Request $request, Response $response) use ($twig) {
        
        return $twig->render($response, 'revisor/mis_revisiones.twig', [
            'user' => [],
            'total' => 0,
            'revisiones' => [],
            'info' => null,
            'error' => null,
        ]);
    });

    $app->get('/protected/revisar/{id}', function (Request $request, Response $response, $args) use ($twig) {

        return $twig->render($response, 'revisor/revisar.twig', [
            'user' => [],
            'revision' => [],
            'error' => null,
            'info' => null,
        ]);
    });

    $app->get('/protected/revision/{id}', function (Request $request, Response $response, $args) use ($twig) {

        return $twig->render($response, 'revisor/revision.twig', [
            'user' => [],
            'revision' => [],
            'articulo' => [],
            'error' => [],
            'info' => [],
        ]);
    });
/**
 * ===================================================================================================
 *                                     ADMINISTRADORES
 * ===================================================================================================
 */
    $app->get('/private/revisores', function (Request $request, Response $response) use ($twig) {

        return $twig->render($response, 'admin/ver_revisores.twig', [
            'user' => [],
            'total' => 0,
            'revisores' => [],
            'categorias' => [],
            'info' => null,
            'error' => null,
        ]);
    });

    $app->get('/private/revisores/nuevo', function (Request $request, Response $response) use ($twig) {

        return $twig->render($response, 'admin/agregar_revisor.twig', [
            'user' => [],
            'info' => [],
            'error' => [],
            'categorias' => [],
        ]);
    });

    $app->get('/private/revisores/{id}', function (Request $request, Response $response, $args) use ($twig) {

        return $twig->render($response, 'admin/editar_revisor.twig', [
            'user' => [],
            'revisor' => [],
            'categorias' => [],
            'info' => [],
            'error' => null,
        ]);
    });

    $app->get('/private/asignaciones/articulos', function (Request $request, Response $response) use ($twig) {

        return $twig->render($response, 'admin/ver_asig_art.twig', [
            'user' => [],
            'info' => null,
            'error' => null,
            'total_art' => 0,
            'articulos' => [],
            'section' => 'articulos',
        ]);
    });

    $app->get('/private/asignaciones/revisores', function (Request $request, Response $response) use ($twig) {

        return $twig->render($response, 'admin/ver_asig_rev.twig', [
            'user' => [],
            'info' => null,
            'error' => null,
            'total_rev' => 0,
            'revisores' => [],
            'section' => 'revisores',
        ]);
    });

    $app->get('/private/asignar_rev/{id_articulo}', function (Request $request, Response $response, $args) use ($twig) {
        
        return $twig->render($response, 'admin/art_to_rev.twig', [
            'user' => [],
            'info' => null,
            'error' => null,
            'articulo' => [],
            'revisores' => [],
        ]);
    });

    $app->get('/private/asignar_art/{id_revisor}', function (Request $request, Response $response, $args) use ($twig) {

        return $twig->render($response, 'admin/rev_to_art.twig', [
            'user' => [],
            'info' => null,
            'error' => null,
            'revisor' => $revisor,
            'articulos' => [],
            'section' => 'revisores',
        ]);
    });
};