<?php
require_once __DIR__ . '/API/responses.php';
require_once __DIR__ . '/API/functions.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use API\ResponseHelper;
use API\Functions;

return function (RouteCollectorProxy $app) {
    /**
     * CRUD ARTICULOS
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

    /**
     * AUTENTIFICACION/AUTORIZACION
     */

};
