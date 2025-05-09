<?php
namespace API;

require_once __DIR__ . '/responses.php';
require_once __DIR__ . '/AuthLevelRouting.php';
require_once __DIR__ . '/functions.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface;
use API\ResponseHelper;
use API\Functions;
use Slim\Psr7\Factory\ResponseFactory;
use SHARED\AuthorizationLevel;
use SHARED\Method;
use function SHARED\getAuthorizationLevel;


class AuthMiddleware {
    protected ResponseFactory $responseFactory;

    public function __construct() {
        $this->responseFactory = new ResponseFactory();
    }

    public function __invoke(Request $request, RequestHandlerInterface $handler): Response {
        $path = $request->getUri()->getPath();
        $method = Method::tryFrom($request->getMethod());
        if (!$method) {
            return ResponseHelper::r_error('Método HTTP no soportado.', 400);
        }
        $routeAuthorizationLevel = getAuthorizationLevel($path, $method);
        if ($routeAuthorizationLevel === AuthorizationLevel::GUEST) {
            return $handler->handle($request);
        }
        $token = Functions::ObtenerToken($request);
        if (!$token) {
            return ResponseHelper::r_error('Token no proporcionado.', 401);
        }
        $userLevel = $this->getAuthorizationLevelFromToken($token);
        if (!$this->isAuthorized($routeAuthorizationLevel, $userLevel)) {
            return ResponseHelper::r_error('No autorizado.', 403);
        }

        return $handler->handle($request);
    }

    public function isAuthorized(AuthorizationLevel $requiredLevel, AuthorizationLevel $userLevel): bool {
        /**
         *        ADMINISTRADOR         | Menos Autoridad
         *     CONTACTO  |  REVISOR     | y acceso a todo
         *             GUEST            V
         */
        switch ($requiredLevel) {
            case AuthorizationLevel::ADMINISTRADOR:
            return $userLevel === AuthorizationLevel::ADMINISTRADOR;
            case AuthorizationLevel::REVISOR:
            return $userLevel === AuthorizationLevel::REVISOR || $userLevel === AuthorizationLevel::ADMINISTRADOR;
            case AuthorizationLevel::CONTACTO:
            return $userLevel === AuthorizationLevel::CONTACTO || $userLevel === AuthorizationLevel::ADMINISTRADOR;
            default:
            return false;
        }
    }

    private function getAuthorizationLevelFromToken(string $token): AuthorizationLevel {
        $decoded = JwtHelper::verificarToken($token);
        if (!$decoded) {
            return AuthorizationLevel::GUEST;
        }
        $tipo = $decoded['tipo'] ?? null;
        switch ($tipo) {
            case 'AUT':
                return AuthorizationLevel::CONTACTO;
            case 'REV':
                return AuthorizationLevel::REVISOR;
            case 'ADM':
                return AuthorizationLevel::ADMINISTRADOR;
            default:
                return AuthorizationLevel::GUEST;
        }
    }
}
