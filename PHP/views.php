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
    $app->get('/', function (Request $request, Response $response) use ($twig) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;
        $info = $queryParams['info'] ?? null;
        foreach ($queryParams as $key => $value) {
            if ($value === '') {
                unset($queryParams[$key]);
            }
        };
        $articulos = Functions::filtrarArticulos($pdo, $queryParams) ?? [];
        $categorias = Functions::obtenerCategorias($pdo) ?? [];
        $revisores = Functions::obtenerRevisores($pdo) ?? [];
        
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        if ($user) {
            $user['es_propietario'] = Functions::esPropietario($pdo, $user['sub']);
        }

        return $twig->render($response, 'home.twig', [
            'user' => $user,
            'total' => count($articulos),
            'articulos' => $articulos,
            'categorias' => $categorias,
            'queryParams' => $queryParams,
            'revisores' => $revisores,
            'error' => $error,
            'info' => $info,
        ]);
    });

    $app->get('/registro', function (Request $request, Response $response) use ($twig) {
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;
        $info = $queryParams['info'] ?? null;
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        if ($user) {
            return ResponseHelper::redirect($response, '/?error=Ya tienes una cuenta, por favor cierra sesión para registrarte.');
        }

        return $twig->render($response, 'registro.twig', [
            'on_log_reg' => true,
            'error' => $error,
            'info' => $info,
        ]);
    });

    $app->get('/login', function (Request $request, Response $response) use ($twig) {
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;
        $info = $queryParams['info'] ?? null;
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        if ($user) {
            return ResponseHelper::redirect($response, '/?error=Por favor cierra sesión para cambiar de cuenta.');
        }
        return $twig->render($response, 'login.twig', [
            'on_log_reg' => true,
            'error' => $error,
            'info' => $info,
        ]);
    });

    $app->get('/protected/mis_articulos', function (Request $request, Response $response) use ($twig) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;
        $info = $queryParams['info'] ?? null;
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        if ($user) {
            $user['es_propietario'] = Functions::esPropietario($pdo, $user['sub']);
        }

        $articulos = Functions::obtenerArticulosPorAutor($pdo, $user['sub']) ?? [];
        $today = date('Y-m-d');
        foreach ($articulos as $key => $articulo) {
            $articulos[$key]['editable'] = strtotime($articulo['fecha_envio']) >= strtotime($today);
        }
        
        return $twig->render($response, 'autor/mis_articulos.twig', [
            'user' => $user,
            'total' => count($articulos),
            'articulos' => $articulos,
            'info' => $info,
            'error' => $error,
        ]);
    });

    $app->get('/protected/publicar', function (Request $request, Response $response) use ($twig) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;
        $info = $queryParams['info'] ?? null;
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        if ($user) {
            $user['es_propietario'] = Functions::esPropietario($pdo, $user['sub']);
        }

        $categorias = Functions::obtenerCategorias($pdo) ?? [];
        $autores = Functions::obtenerAutores($pdo) ?? [];
        
        return $twig->render($response, 'autor/publicar.twig', [
            'user' => $user,
            'topicos' => $categorias,
            'autores' => $autores,
            'error' => $error,
            'info' => $info,
        ]);
    });

    $app->get('/protected/mis_articulos/{id}', function (Request $request, Response $response, $args) use ($twig) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;
        $info = $queryParams['info'] ?? null;
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        if ($user) {
            $user['es_propietario'] = Functions::esPropietario($pdo, $user['sub']);
        }

        $detalles = Functions::obtenerDetallesArticulo($pdo, $args['id']);
        $articulo = Functions::obtenerArticuloCompleto($pdo, $args['id']);
        $detallesFixed = [];

        foreach ($detalles as $detalle) {
            $argumentos = json_decode($detalle['argumentos'] ?? '', true) ?? [];
            $detalleFix = $detalle;
            $detalleFix['argumentos'] = [];
            foreach ($argumentos as $tema => $motivo) {
                $detalleFix['argumentos'][] = [
                    'tema' => $tema,
                    'motivo' => $motivo,
                ];
            }
            $detallesFixed[] = $detalleFix;
        }

        return $twig->render($response, 'autor/detalles.twig', [
            'user' => $user,
            'articulo' => $articulo,
            'detalles' => $detallesFixed,
            'info' => $info,
            'error' => $error,
        ]);
    });

    $app->get('/protected/mis_articulos/{id}/editar', function (Request $request, Response $response, $args) use ($twig) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;
        $info = $queryParams['info'] ?? null;
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        if ($user) {
            $user['es_propietario'] = Functions::esPropietario($pdo, $user['sub']);
        }

        $categorias = Functions::obtenerCategorias($pdo) ?? [];
        $autores = Functions::obtenerAutores($pdo) ?? [];
        $articulo = Functions::obtenerArticuloCompleto($pdo, $args['id']);
        $topicosAsociados = Functions::obtenerTopicos($pdo, $args['id']) ?? [];
        $autoresAsociados = Functions::obtenerAutoresAsociados($pdo, $args['id']) ?? [];

        return $twig->render($response, 'autor/editar_articulo.twig', [
            'user' => $user,
            'articulo' => $articulo,
            'topicos' => $categorias,
            'topicos_asociados' => $topicosAsociados,
            'autores' => $autores,
            'autores_asociados' => $autoresAsociados,
            'error' => $error,
            'info' => $info,
        ]);
    });

    $app->get('/mi_cuenta', function (Request $request, Response $response) use ($twig) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;
        $info = $queryParams['info'] ?? null;
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        if ($user) {
            $user['es_propietario'] = Functions::esPropietario($pdo, $user['sub']);
        }

        return $twig->render($response, 'mi_cuenta.twig', [
            'user' => $user,
            'info' => $info,
            'error' => $error,
        ]);
    });

    $app->get('/editar_cuenta', function (Request $request, Response $response) use ($twig) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;
        $info = $queryParams['info'] ?? null;
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        if ($user) {
            $user['es_propietario'] = Functions::esPropietario($pdo, $user['sub']);
        }

        return $twig->render($response, 'editar.twig', [
            'user' => $user,
            'error' => $error,
            'info' => $info,
        ]);
    });

    $app->get('/articulo/{id}', function (Request $request, Response $response, $args) use ($twig) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;
        $info = $queryParams['info'] ?? null;
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        if ($user) {
            $user['es_propietario'] = Functions::esPropietario($pdo, $user['sub']);
        }

        $articulo = Functions::obtenerArticuloFull($pdo, $args['id']);

        return $twig->render($response, 'articulo.twig', [
            'user' => $user,
            'articulo' => $articulo,
            'error' => $error,
            'info' => $info,
        ]);
    });

    $app->get('/protected/mis_revisiones', function (Request $request, Response $response) use ($twig) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;
        $info = $queryParams['info'] ?? null;
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        if ($user) {
            $user['es_propietario'] = Functions::esPropietario($pdo, $user['sub']);
        }

        $revisiones = Functions::obtenerMisRevisiones($pdo, $user['sub']) ?? [];
        
        return $twig->render($response, 'revisor/mis_revisiones.twig', [
            'user' => $user,
            'total' => count($revisiones),
            'revisiones' => $revisiones,
            'info' => $info,
            'error' => $error,
        ]);
    });

    $app->get('/protected/revisar/{id}', function (Request $request, Response $response, $args) use ($twig) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;
        $info = $queryParams['info'] ?? null;
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        if ($user) {
            $user['es_propietario'] = Functions::esPropietario($pdo, $user['sub']);
        }

        $revision = Functions::obtenerRevisionFull($pdo, $user['sub'], $args['id']);

        return $twig->render($response, 'revisor/revisar.twig', [
            'user' => $user,
            'revision' => $revision,
            'error' => $error,
            'info' => $info,
        ]);
    });

};