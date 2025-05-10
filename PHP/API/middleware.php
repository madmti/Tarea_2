<?php
namespace API;

require_once __DIR__ . '/responses.php';
require __DIR__ . '/AuthLevelRouting.php';
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
use SHARED\routes;


class AuthMiddleware {
    protected ResponseFactory $responseFactory;
    protected array $routes;

    public function __construct(array $routes) {
        $this->responseFactory = new ResponseFactory();
        $this->routes = $routes;
    }

    public function __invoke(Request $request, RequestHandlerInterface $handler): Response {
        $path = $request->getUri()->getPath();
        $method = Method::tryFrom($request->getMethod());
        if (!$method) {
            return ResponseHelper::r_error('Método HTTP no soportado.', 400);
        }
        $routeAuthorizationLevels = getAuthorizationLevel($path, $method, $this->routes);
        if (is_array($routeAuthorizationLevels) && in_array(AuthorizationLevel::GUEST, $routeAuthorizationLevels, true) || $routeAuthorizationLevels == AuthorizationLevel::GUEST) {
            return $handler->handle($request);
        }
        $token = Functions::ObtenerToken($request);
        if (!$token) {
            return ResponseHelper::r_error('Token no proporcionado.', 401);
        }
        $userLevel = $this->getAuthorizationLevelFromToken($token);
        if (!$this->isAuthorized($routeAuthorizationLevels, $userLevel)) {
            return ResponseHelper::r_error('No autorizado.', 403);
        }

        return $handler->handle($request);
    }

    public function isAuthorized(array $requiredLevels, AuthorizationLevel $userLevel): bool {
        /**
         *        ADMINISTRADOR         | Menos Autoridad
         *     CONTACTO  |  REVISOR     | y acceso a todo
         *             GUEST            V
         */
        foreach ($requiredLevels as $requiredLevel) {
            switch ($requiredLevel) {
                case AuthorizationLevel::ADMINISTRADOR:
                    if ($userLevel === AuthorizationLevel::ADMINISTRADOR) {
                        return true;
                    }
                    break;
                case AuthorizationLevel::REVISOR:
                    if ($userLevel === AuthorizationLevel::REVISOR || $userLevel === AuthorizationLevel::ADMINISTRADOR) {
                        return true;
                    }
                    break;
                case AuthorizationLevel::CONTACTO:
                    if ($userLevel === AuthorizationLevel::CONTACTO || $userLevel === AuthorizationLevel::ADMINISTRADOR) {
                        return true;
                    }
                    break;
            }
        }
        return false;
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
