<?php
require_once __DIR__ . '/API/responses.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use API\ResponseHelper;

return function (RouteCollectorProxy $app) {
    // Articulos
    $app->get('/public/articulos', function (Request $request, Response $response) {
        $pdo = $this->get('db');
        $queryParams = $request->getQueryParams();

        $stmt = $pdo->prepare("CALL filtrar_articulos(:autor, :fecha_ini, :fecha_fin, :categoria, :revisor, :titulo)");
        $stmt->execute([
            'autor' => $queryParams['autor'] ?? null,               // ID autor
            'fecha_ini' => $queryParams['desde'] ?? null,           // DATE
            'fecha_fin' => $queryParams['hasta'] ?? null,           // DATE
            'categoria' => $queryParams['categoria_id'] ?? null,    // ID categoria
            'revisor' => $queryParams['revisor_id'] ?? null,        // ID revisor
            'titulo' => $queryParams['titulo'] ?? null              // STRING
        ]);
        $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        if ($articulos) {
            $data = [
                'total' => count($articulos),
                'articulos' => $articulos,
            ];
        } else {
            $data = ['message' => 'No se encontraron articulos con ese titulo.'];
        }
    
        return ResponseHelper::json($response, $data);
    });



};
