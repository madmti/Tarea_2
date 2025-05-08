<?php
require_once __DIR__ . '/API/responses.php';
require_once __DIR__ . '/API/functions.php';
require_once __DIR__ . '/API/jwt.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use API\ResponseHelper;
use API\Functions;
use API\JwtHelper;

return function (RouteCollectorProxy $app) {
    /**
     * ================================================================
     *                        CRUD ARTICULOS
     * ================================================================
     */
    $app->get('/public/articulos', function (Request $request, Response $response) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();

        $articulos = Functions::filtrarArticulos($pdo, $queryParams);
        $data = [
            'message' => 'Artículos obtenidos exitosamente.',
            'total' => count($articulos) ?? 0,
            'articulos' => $articulos ?? [],
        ];
        return ResponseHelper::json($response, $data);
    });

    $app->post('/public/articulos', function (Request $request, Response $response) {
        $pdo = $this->get('db');
        $data = json_decode($request->getBody(), true);
    
        if (empty($data['titulo']) || empty($data['resumen']) || empty($data['topicos']) || empty($data['autores'])) {
            return ResponseHelper::error($response, 'Faltan datos obligatorios.', 400);
        }
        if (!is_array($data['topicos']) || !is_array($data['autores'])) {
            return ResponseHelper::error($response, 'Los topicos y autores deben ser arrays.', 400);
        }
        if (count($data['topicos']) < 1 || count($data['autores']) < 1) {
            return ResponseHelper::error($response, 'Se requiere al menos un topico y un autor.', 400);
        }
        if (strlen($data['titulo']) > 50) {
            return ResponseHelper::error($response, 'El título no puede exceder los 50 caracteres.', 400);
        }
        if (strlen($data['resumen']) > 150) {
            return ResponseHelper::error($response, 'El resumen no puede exceder los 150 caracteres.', 400);
        }
        
        $idArticulo = Functions::insertarArticulo($pdo, $data);
        if (!$idArticulo) {
            return ResponseHelper::error($response, 'Error al crear el artículo.', 500);
        }
        $res_top = Functions::insertarTopicos($pdo, $idArticulo, $data['topicos']);
        if (!$res_top) {
            return ResponseHelper::error($response, 'Error al insertar los tópicos.', 500);
        }
        $res_aut = Functions::insertarAutores($pdo, $idArticulo, $data['autores']);
        if (!$res_aut) {
            return ResponseHelper::error($response, 'Error al insertar los autores.', 500);
        }
    
        return ResponseHelper::json($response, [
            'message' => 'Artículo creado exitosamente.',
            'id_articulo' => $idArticulo
        ]);
    });

    $app->put('/protected/articulos/{id}', function (Request $request, Response $response, array $args) {
        $pdo = $this->get('db');
        $data = json_decode($request->getBody(), true);
        $idArticulo = (int)$args['id'];

        if (empty($data['titulo']) || empty($data['resumen'])) {
            return ResponseHelper::error($response, 'Faltan datos obligatorios.', 400);
        }
        if (strlen($data['titulo']) > 50) {
            return ResponseHelper::error($response, 'El título no puede exceder los 50 caracteres.', 400);
        }
        if (strlen($data['resumen']) > 150) {
            return ResponseHelper::error($response, 'El resumen no puede exceder los 150 caracteres.', 400);
        }

        $res = Functions::actualizarArticulo($pdo, $idArticulo, $data);
        if (!$res) {
            return ResponseHelper::error($response, 'Error al actualizar el artículo.', 500);
        }

        return ResponseHelper::json($response, [
            'message' => 'Artículo actualizado exitosamente.',
            'id_articulo' => $idArticulo
        ]);
    });

    $app->delete('/protected/articulos/{id}', function (Request $request, Response $response, array $args) {
        $pdo = $this->get('db');
        $idArticulo = (int)$args['id'];

        $res = Functions::eliminarArticulo($pdo, $idArticulo);
        if (!$res) {
            return ResponseHelper::error($response, 'Error al eliminar el artículo.', 500);
        }

        return ResponseHelper::json($response, [
            'message' => 'Artículo eliminado exitosamente.'
        ]);
    });

    /**
     * ================================================================
     *                 AUTENTIFICACION/AUTORIZACION
     * ================================================================
     */
    $app->post('/public/registro', function (Request $request, Response $response) {
        $pdo = $this->get('db');
        $data = json_decode($request->getBody(), true);

        if (empty($data['rut']) || empty($data['email']) || empty($data['nombre']) || empty($data['contrasena'])) {
            return ResponseHelper::error($response, 'Faltan datos obligatorios.', 400);
        }
        if (strlen($data['rut']) > 10) {
            return ResponseHelper::error($response, 'El RUT no puede exceder los 10 caracteres.', 400);
        }
        if (strlen($data['email']) > 95) {
            return ResponseHelper::error($response, 'El email no puede exceder los 95 caracteres.', 400);
        }
        if (strlen($data['nombre']) > 85) {
            return ResponseHelper::error($response, 'El nombre no puede exceder los 85 caracteres.', 400);
        }
        if (strlen($data['contrasena']) > 30) {
            return ResponseHelper::error($response, 'La contraseña no puede exceder los 30 caracteres.', 400);
        }

        $idUsuario = Functions::registrarUsuarioAutor($pdo, $data);
        if (is_null($idUsuario)) {
            return ResponseHelper::error($response, 'Error al registrar el usuario.', 500);
        }

        $token = JwtHelper::generarToken($idUsuario, 'AUT');

        return ResponseHelper::json($response, [
            'message' => 'Usuario registrado exitosamente.',
            'id_usuario' => $idUsuario,
        ])->withHeader('Authorization', 'Bearer ' . $token);
    });

    $app->post('/private/registro', function (Request $request, Response $response) {
        $pdo = $this->get('db');
        $data = json_decode($request->getBody(), true);

        if (empty($data['rut']) || empty($data['email']) || empty($data['nombre']) || empty($data['contrasena']) || empty($data['tipo'])) {
            return ResponseHelper::error($response, 'Faltan datos obligatorios.', 400);
        }
        if (strlen($data['rut']) > 10) {
            return ResponseHelper::error($response, 'El RUT no puede exceder los 10 caracteres.', 400);
        }
        if (strlen($data['email']) > 95) {
            return ResponseHelper::error($response, 'El email no puede exceder los 95 caracteres.', 400);
        }
        if (strlen($data['nombre']) > 85) {
            return ResponseHelper::error($response, 'El nombre no puede exceder los 85 caracteres.', 400);
        }
        if (strlen($data['contrasena']) > 30) {
            return ResponseHelper::error($response, 'La contraseña no puede exceder los 30 caracteres.', 400);
        }
        if (!in_array($data['tipo'], ['AUT', 'REV', 'ADM'])) {
            return ResponseHelper::error($response, 'Tipo de usuario inválido.', 400);
        }

        $idUsuario = Functions::registrarUsuario($pdo, $data);
        if (is_null($idUsuario)) {
            return ResponseHelper::error($response, 'Error al registrar el usuario.', 500);
        }
        $token = JwtHelper::generarToken($idUsuario, $data['tipo']);

        return ResponseHelper::json($response, [
            'message' => 'Usuario registrado exitosamente.',
            'id_usuario' => $idUsuario,
        ])->withHeader('Authorization', 'Bearer ' . $token);
    });

    $app->post('/public/login', function (Request $request, Response $response) {
        $pdo = $this->get('db');
        $data = json_decode($request->getBody(), true);

        if (empty($data['email']) || empty($data['contrasena'])) {
            return ResponseHelper::error($response, 'Faltan datos obligatorios.', 400);
        }
        if (strlen($data['email']) > 95) {
            return ResponseHelper::error($response, 'El email no puede exceder los 95 caracteres.', 400);
        }
        if (strlen($data['contrasena']) > 30) {
            return ResponseHelper::error($response, 'La contraseña no puede exceder los 30 caracteres.', 400);
        }

        $token = Functions::loginUsuario($pdo, $data['email'], $data['contrasena']);
        if (!$token) {
            return ResponseHelper::error($response, 'Error al iniciar sesión.', 401);
        }

        return ResponseHelper::json($response, [
            'message' => 'Inicio de sesión exitoso.',
        ])->withHeader('Authorization', 'Bearer ' . $token);
    });

    
};
