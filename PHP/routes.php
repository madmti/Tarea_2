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

        return $twig->render($response, 'home.twig', [
            'user' => $user,
            'total' => count($articulos),
            'articulos' => $articulos,
            'categorias' => $categorias,
            'queryParams' => $queryParams,
            'revisores' => $revisores,
        ]);
    });


    $app->get('/registro', function (Request $request, Response $response) use ($twig) {
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;

        return $twig->render($response, 'registro.twig', [
            'on_log_reg' => true,
            'error' => $error,
        ]);
    });

    $app->post('/registro', function ($request, $response) {
        $pdo = $this->get('db');
        $data = $request->getParsedBody();
    
        if (empty($data['rut']) || empty($data['email']) || empty($data['nombre']) || empty($data['contrasena'])) {
            return ResponseHelper::redirect(
                $response,
                '/registro',
            );
        }

        if (!preg_match('/^\d{7,8}-[0-9Kk]$/', $data['rut'])) {
            return ResponseHelper::redirect(
            $response,
            '/registro?error=RUT invalido.',
            );
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ResponseHelper::redirect(
                $response,
                '/registro?error=Email invalido.',
            );
        }
    
        $idUsuario = Functions::registrarUsuarioAutor($pdo, $data);
    
        if (!$idUsuario) {
            return ResponseHelper::redirect(
                $response,
                '/registro?error=Error al registrar el usuario.',
            );
        }

        $token = JwtHelper::generarToken($idUsuario, 'AUT', $data['nombre'], $data['email']);
        if (!$token) {
            return ResponseHelper::redirect(
                $response,
                '/registro?error=Error al generar el token.',
            );
        }
        $response = ResponseHelper::setTokenCookie($response, $token);
        return ResponseHelper::redirect(
            $response,
            '/',
        );
    });

    $app->get('/logout', function (Request $request, Response $response) {
        $response = ResponseHelper::deleteTokenCookie($response);
        return ResponseHelper::redirect(
            $response,
            '/',
        );
    });

    $app->get('/login', function (Request $request, Response $response) use ($twig) {
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;
        return $twig->render($response, 'login.twig', [
            'on_log_reg' => true,
            'error' => $error,
        ]);
    });

    $app->post('/login', function (Request $request, Response $response) {
        $pdo = $this->get('db');
        $data = $request->getParsedBody();

        if (empty($data['email']) || empty($data['contrasena'])) {
            return ResponseHelper::redirect(
                $response,
                '/login?error=Email y contraseña son obligatorios.',
            );
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ResponseHelper::redirect(
                $response,
                '/login?error=Email invalido.',
            );
        }

        $token = Functions::loginUsuario($pdo, $data['email'], $data['contrasena']);
        if (!$token) {
            return ResponseHelper::redirect(
                $response,
                '/login?error=Credenciales incorrectas.',
            );
        }

        $response = ResponseHelper::setTokenCookie($response, $token);
        return ResponseHelper::redirect(
            $response,
            '/',
        );
    });

    $app->get('/protected/mis_articulos', function (Request $request, Response $response) use ($twig) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();
        $info = $queryParams['info'] ?? null;

        
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        $articulos = Functions::obtenerArticulosPorAutor($pdo, $user['sub']) ?? [];
        
        return $twig->render($response, 'autor/mis_articulos.twig', [
            'user' => $user,
            'total' => count($articulos),
            'articulos' => $articulos,
            'info' => $info,
        ]);
    });

    $app->get('/protected/publicar', function (Request $request, Response $response) use ($twig) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;

        $categorias = Functions::obtenerCategorias($pdo) ?? [];
        $autores = Functions::obtenerAutores($pdo) ?? [];
        
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);

        return $twig->render($response, 'autor/publicar.twig', [
            'user' => $user,
            'topicos' => $categorias,
            'autores' => $autores,
            'error' => $error,
        ]);
    });

    $app->post('/protected/publicar', function ($request, $response) {
        $pdo = $this->get('db');
        $data = $request->getParsedBody();
    
        if (empty($data['titulo']) || empty($data['resumen']) || empty($data['topicos']) || empty($data['autores'])) {
            return ResponseHelper::redirect($response, '/protected/publicar?error=Faltan datos obligatorios.');
        }
        if (strlen($data['titulo']) > 50 || strlen($data['resumen']) > 150) {
            return ResponseHelper::redirect($response, '/protected/publicar?error=El título o resumen excede el largo permitido.');
        }    
        if (!is_array($data['topicos']) || !is_array($data['autores'])) {
            return ResponseHelper::redirect($response, '/protected/publicar?error=Error en los datos enviados.');
        }
        if (count($data['topicos']) < 1 || count($data['autores']) < 1) {
            return ResponseHelper::redirect($response, '/protected/publicar?error=Debe seleccionar al menos un tópico y un autor.');
        }
    
        $contacto = $data['autor_contacto'] ?? null;
        if (!$contacto || !in_array($contacto, $data['autores'])) {
            return ResponseHelper::redirect($response, '/protected/publicar?error=Debe seleccionar un autor de contacto válido.');
        }

        $contacto = (int)$contacto;
        $data['autores'] = array_map('intval', $data['autores']);
        $data['topicos'] = array_map('intval', $data['topicos']);
    
        $idArticulo = Functions::insertarArticulo($pdo, [
            'titulo' => $data['titulo'],
            'resumen' => $data['resumen'],
            'contacto' => $contacto
        ]);

        if (!$idArticulo) {
            return ResponseHelper::redirect($response, '/protected/publicar?error=Error al crear el artículo.');
        }

        $res_top = Functions::insertarTopicos($pdo, $idArticulo, $data['topicos']);
        if (!$res_top) {
            return ResponseHelper::redirect($response, '/protected/publicar?error=Error al insertar los tópicos.');
        }

        $res_aut = Functions::insertarPropiedad($pdo, $idArticulo, $data['autores'], $contacto);
        if (!$res_aut) {
            return ResponseHelper::redirect($response, '/protected/publicar?error=Error al insertar los autores.');
        }

        return ResponseHelper::redirect($response, '/protected/mis_articulos?info=Correo enviado con las credenciales.');
    });

    $app->get('/protected/mis_articulos/{id}', function (Request $request, Response $response, $args) use ($twig) {
        $pdo = $this->get('db');

        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);

        return $twig->render($response, 'autor/detalles.twig', [
            'user' => $user,
        ]);
    });

    $app->get('/protected/mis_articulos/{id}/editar', function (Request $request, Response $response, $args) use ($twig) {
        $pdo = $this->get('db');

        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);

        return $twig->render($response, 'autor/editar_articulo.twig', [
            'user' => $user,
        ]);
    });

    $app->get('/mi_cuenta', function (Request $request, Response $response) use ($twig) {
        $pdo = $this->get('db');

        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);

        return $twig->render($response, 'mi_cuenta.twig', [
            'user' => $user,
        ]);
    });

    $app->get('/editar_cuenta', function (Request $request, Response $response) use ($twig) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();
        $error = $queryParams['error'] ?? null;

        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);

        return $twig->render($response, 'editar.twig', [
            'user' => $user,
            'error' => $error,
        ]);
    });

    $app->post('/editar_cuenta', function (Request $request, Response $response) {
        $pdo = $this->get('db');
        $data = $request->getParsedBody();

        if (empty($data['nombre']) || empty($data['email']) || empty($data['contrasena_actual'])) {
            return ResponseHelper::redirect($response, '/editar_cuenta?error=Faltan datos obligatorios.');
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ResponseHelper::redirect($response, '/editar_cuenta?error=Email invalido.');
        }
        if (strlen($data['nombre']) > 50) {
            return ResponseHelper::redirect($response, '/editar_cuenta?error=El nombre excede el largo permitido.');
        }
        if (isset($data['contrasena_nueva']) && strlen($data['contrasena_nueva']) < 8) {
            return ResponseHelper::redirect($response, '/editar_cuenta?error=La nueva contraseña es demasiado corta.');
        }
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        $idUsuario = $user['sub'];

        $res = Functions::confirmarContrasena($pdo, $idUsuario, $data['contrasena_actual']);
        if (!$res) {
            return ResponseHelper::redirect($response, '/editar_cuenta?error=La contraseña actual es incorrecta.');
        }

        $newData = [
            'nombre' => $data['nombre'],
            'email' => $data['email'],
        ];
        $newData['contrasena'] = isset($data['contrasena_nueva']) ? $data['contrasena_nueva'] : null;
        $newUser = Functions::actualizarUsuario($pdo, $idUsuario, $newData);
        if (!$newUser) {
            return ResponseHelper::redirect($response, '/editar_cuenta?error=Error al actualizar la cuenta.');
        }

        $token = JwtHelper::generarToken($idUsuario, $user['tipo'], $data['nombre'], $data['email']);
        if (!$token) {
            return ResponseHelper::redirect($response, '/editar_cuenta?error=Error al generar el token.');
        }
        $response = ResponseHelper::setTokenCookie($response, $token);
        return ResponseHelper::redirect($response, '/mi_cuenta');
    });

    $app->get('/articulo/{id}', function (Request $request, Response $response, $args) use ($twig) {
        $pdo = $this->get('db');

        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);

        $articulo = Functions::obtenerArticuloFull($pdo, $args['id']);

        return $twig->render($response, 'articulo.twig', [
            'user' => $user,
            'articulo' => $articulo,
        ]);
    });
};
