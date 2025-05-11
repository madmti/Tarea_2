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
 *                                      CUENTAS DE USUARIO
 * ===================================================================================================
 */
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

    $app->post('/eliminar_cuenta', function (Request $request, Response $response) {
    $pdo = $this->get('db');
    $token = Functions::ObtenerToken($request);
    $user = Functions::verificarAuthUsuario($token);
    
    if (!$user) {
        return ResponseHelper::redirect(
            $response,
            '/',
        );
    }
    
    $params = $request->getParsedBody();
    $idUsuarioAEliminar = $params['id_usuario'] ?? $user['sub'];
    
    if ($idUsuarioAEliminar != $user['sub'] && $user['tipo'] !== 'ADM') {
        return ResponseHelper::redirect(
            $response,
            '/mi_cuenta?error=No tienes permiso para eliminar esta cuenta.',
        );
    }
    
    $res = Functions::borrarUsuario($pdo, $idUsuarioAEliminar);
    if (!$res) {
        return ResponseHelper::redirect(
            $response,
            '/mi_cuenta?error=Error al eliminar la cuenta.',
        );
    }
    if ($idUsuarioAEliminar == $user['sub']) {
        $response = ResponseHelper::deleteTokenCookie($response);
        return ResponseHelper::redirect(
            $response,
            '/',
        );
    } else {
        return ResponseHelper::redirect(
            $response,
            '/',
        );
    }
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

/**
 * ===================================================================================================
 *                                        ARTICULOS
 * ===================================================================================================
 */
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
            print_r($contacto);
            print_r($data['autores']);
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

        $res_revs = Functions::asignarRevisores($pdo, $idArticulo);
        if (!$res_revs) {
            // TODO AVISAR: No se asignaron revisores, pero el artículo se creó correctamente.
        }

        return ResponseHelper::redirect($response, '/protected/mis_articulos?info=Correo enviado con las credenciales.');
    });

    $app->post('/protected/revisar/{id_articulo}', function ($request, $response, $args) {
        $pdo = $this->get('db');
        $idArticulo = (int) $args['id_articulo'];
        $token = Functions::ObtenerToken($request);
        $user = Functions::verificarAuthUsuario($token);
        $data = $request->getParsedBody();

        if (
            !isset($data['calidad_tecnica'], $data['originalidad'], $data['valoracion_global'], $data['argumentos'], $data['estado'])
        ) {
            return ResponseHelper::redirect($response, "/protected/revisar/$idArticulo?msg=Faltan campos obligatorios");
        }

        $calidad = (int) $data['calidad_tecnica'];
        $originalidad = (int) $data['originalidad'];
        $global = (int) $data['valoracion_global'];
        $estado = (int) $data['estado'];
        $argumentosJson = $data['argumentos'];

        if (
            $calidad < 1 || $calidad > 10 ||
            $originalidad < 1 || $originalidad > 10 ||
            $global < 1 || $global > 10
        ) {
            return ResponseHelper::redirect($response, "/protected/revisar/$idArticulo?msg=Las valoraciones deben estar entre 1 y 10");
        }

        $argumentos = json_decode($argumentosJson, true);
        if (!is_array($argumentos) || count($argumentos) < 1) {
            print_r($argumentos);
            return ResponseHelper::redirect($response, "/protected/revisar/$idArticulo?msg=Los argumentos deben ser válidos y contener al menos uno");
        }

        $res = Functions::actualizarRevision($pdo, $idArticulo, $user['sub'], [
            'calidad_tecnica' => $calidad,
            'originalidad' => $originalidad,
            'valoracion_global' => $global,
            'estado' => $estado,
            'argumentos' => $argumentos,
        ]);
        if (!$res) {
            return ResponseHelper::redirect($response, "/protected/revisar/$idArticulo?msg=Error al enviar la revisión");
        }

        return ResponseHelper::redirect($response, '/protected/mis_revisiones?msg=Revisión enviada correctamente');
    });
    
    $app->post('/protected/mis_articulos/{id}/editar', function ($request, $response, $args) {
        $pdo = $this->get('db');
        $data = $request->getParsedBody();
    
        if (empty($data['titulo']) || empty($data['resumen']) || empty($data['topicos']) || empty($data['autores'])) {
            return ResponseHelper::redirect($response, "/protected/mis_articulos/{$args['id']}/editar?error=Faltan datos obligatorios.");
        }
        if (strlen($data['titulo']) > 50 || strlen($data['resumen']) > 150) {
            return ResponseHelper::redirect($response, "/protected/mis_articulos/{$args['id']}/editar?error=El título o resumen excede el largo permitido.");
        }    
        if (!is_array($data['topicos']) || !is_array($data['autores'])) {
            return ResponseHelper::redirect($response, "/protected/mis_articulos/{$args['id']}/editar?error=Error en los datos enviados.");
        }
        if (count($data['topicos']) < 1 || count($data['autores']) < 1) {
            return ResponseHelper::redirect($response, "/protected/mis_articulos/{$args['id']}/editar?error=Debe seleccionar al menos un tópico y un autor.");
        }
    
        $contacto = $data['autor_contacto'] ?? null;
        if (!$contacto || !in_array($contacto, $data['autores'])) {
            return ResponseHelper::redirect($response, "/protected/mis_articulos/{$args['id']}/editar?error=Debe seleccionar un autor de contacto válido.");
        }

        $contacto = (int)$contacto;
        $data['autores'] = array_map('intval', $data['autores']);
        $data['topicos'] = array_map('intval', $data['topicos']);
        $idArticulo = (int)$args['id'];
    
        $res = Functions::actualizarArticulo($pdo, $idArticulo, $data);
        if (!$res) {
            return ResponseHelper::redirect($response, "/protected/mis_articulos/{$args['id']}/editar?error=Error al actualizar el artículo.");
        }
        $res_top = Functions::actualizarTopicos($pdo, $idArticulo, $data['topicos']);
        if (!$res_top) {
            return ResponseHelper::redirect($response, "/protected/mis_articulos/{$args['id']}/editar?error=Error al actualizar los tópicos.");
        }
        $res_aut = Functions::actualizarPropiedad($pdo, $idArticulo, $data['autores'], $contacto);
        if (!$res_aut) {
            return ResponseHelper::redirect($response, "/protected/mis_articulos/{$args['id']}/editar?error=Error al actualizar los autores.");
        }
        return ResponseHelper::redirect($response, '/protected/mis_articulos?info=Artículo actualizado correctamente.');
    });

/**
 * ===================================================================================================
 *                                        ADM -> REVISORES
 * ===================================================================================================
 */
    $app->post('/private/revisores/nuevo', function ($request, $response) {
        $pdo = $this->get('db');
        $data = $request->getParsedBody();
    
        if (empty($data['rut']) || empty($data['email']) || empty($data['nombre']) || empty($data['contrasena']) || empty($data['especialidades'])) {
            return ResponseHelper::redirect($response, '/private/revisores/nuevo?error=Faltan datos obligatorios.');
        }
        if (!preg_match('/^\d{7,8}-[0-9Kk]$/', $data['rut'])) {
            return ResponseHelper::redirect($response, '/private/revisores/nuevo?error=RUT invalido.');
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 50) {
            return ResponseHelper::redirect($response, '/private/revisores/nuevo?error=Email invalido.');
        }
        if (!is_array($data['especialidades']) || count($data['especialidades']) < 1) {
            return ResponseHelper::redirect($response, '/private/revisores/nuevo?error=Debe seleccionar al menos una especialidad.');
        }
        if (strlen($data['nombre']) > 85) {
            return ResponseHelper::redirect($response, '/private/revisores/nuevo?error=El nombre excede el largo permitido.');
        }
        if (strlen($data['contrasena']) < 8) {
            return ResponseHelper::redirect($response, '/private/revisores/nuevo?error=La contraseña es demasiado corta.');
        }

        $data['especialidades'] = array_map('intval', $data['especialidades']);
        $data['tipo'] = 'REV';
        $idUsuario = Functions::registrarUsuario($pdo, $data);
        if (!$idUsuario) {
            return ResponseHelper::redirect($response, '/private/revisores/nuevo?error=Error al registrar el revisor.');
        }
        $res = Functions::insertarEspecialidades($pdo, $idUsuario, $data['especialidades']);
        if (!$res) {
            return ResponseHelper::redirect($response, '/private/revisores/nuevo?error=Error al insertar las especialidades.');
        }
        return ResponseHelper::redirect($response, '/private/revisores?info=Revisor creado correctamente.');
    });

    $app->post('/private/revisores/editar/{id}', function ($request, $response, $args) {
        $pdo = $this->get('db');
        $data = $request->getParsedBody();
        $idUsuario = (int)$args['id'];

        if (empty($data['nombre']) || empty($data['email']) || empty($data['especialidades'])) {
            return ResponseHelper::redirect($response, "/private/revisores/editar/{$idUsuario}?error=Faltan datos obligatorios.");
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 50) {
            return ResponseHelper::redirect($response, "/private/revisores/editar/{$idUsuario}?error=Email invalido.");
        }
        if (!is_array($data['especialidades']) || count($data['especialidades']) < 1) {
            return ResponseHelper::redirect($response, "/private/revisores/editar/{$idUsuario}?error=Debe seleccionar al menos una especialidad.");
        }
        if (strlen($data['nombre']) > 85) {
            return ResponseHelper::redirect($response, "/private/revisores/editar/{$idUsuario}?error=El nombre excede el largo permitido.");
        }

        $data['especialidades'] = array_map('intval', $data['especialidades']);
    
        $res = Functions::actualizarUsuario($pdo, $idUsuario, $data);
        if (!$res) {
            return ResponseHelper::redirect($response, "/private/revisores/editar/{$idUsuario}?error=Error al actualizar el revisor.");
        }
        $res = Functions::actualizarEspecialidades($pdo, $idUsuario, $data['especialidades']);
        if (!$res) {
            return ResponseHelper::redirect($response, "/private/revisores/editar/{$idUsuario}?error=Error al actualizar las especialidades.");
        }
    
        return ResponseHelper::redirect($response, '/private/revisores?info=Revisor actualizado correctamente.');
    });

    $app->post('/private/revisores/quitar/{id}', function ($request, $response, $args) {
        $pdo = $this->get('db');
        $idUsuario = (int)$args['id'];
    
        if (empty($idUsuario)) {
            return ResponseHelper::redirect($response, '/private/revisores?error=ID de revisor inválido.');
        }
    
        $res = Functions::borrarUsuario($pdo, $idUsuario);
        if (!$res) {
            return ResponseHelper::redirect($response, '/private/revisores?error=Error al eliminar el revisor.');
        }
    
        return ResponseHelper::redirect($response, '/private/revisores?info=Revisor eliminado correctamente.');
    });
/**
 * ===================================================================================================
 *                                        ADM -> ASIGNACION
 * ===================================================================================================
 */
    $app->post('/private/quitar_art/{id_articulo}/revisor/{id_revisor}/{section}', function ($request, $response, $args) {
        $pdo = $this->get('db');
        $idArticulo = (int)$args['id_articulo'];
        $idRevisor = (int)$args['id_revisor'];
        $section = $args['section'];

        if (empty($idArticulo) || empty($idRevisor)) {
            return ResponseHelper::redirect($response, '/private/asignaciones/articulos?error=ID de artículo o revisor inválido.');
        }

        $res = Functions::quitarAsignacion($pdo, $idArticulo, $idRevisor);
        if (!$res) {
            return ResponseHelper::redirect($response, '/private/asignaciones/articulos?error=Error al quitar la asignación.');
        }

        $path = $section === 'revisores' ? '/private/asignaciones/revisores' : '/private/asignaciones/articulos';

        return ResponseHelper::redirect($response, $path . '?info=Asignación eliminada correctamente.');
    });

    $app->post('/private/asignar_art/{id_articulo}/revisor/{id_revisor}/{section}', function ($request, $response, $args) {
        $pdo = $this->get('db');
        $idArticulo = (int)$args['id_articulo'];
        $idRevisor = (int)$args['id_revisor'];

        if (empty($idArticulo) || empty($idRevisor)) {
            return ResponseHelper::redirect($response, '/private/asignaciones/articulos?error=ID de artículo o revisor inválido.');
        }

        $res = Functions::asignarArticulo($pdo, $idArticulo, $idRevisor);
        if (!$res) {
            return ResponseHelper::redirect($response, '/private/asignaciones/articulos?error=Error al asignar el artículo.');
        }
        $path = $args['section'] === 'revisores' ? '/private/asignaciones/revisores' : '/private/asignaciones/articulos';

        return ResponseHelper::redirect($response, $path . '?info=Asignación realizada correctamente.');
    });
};