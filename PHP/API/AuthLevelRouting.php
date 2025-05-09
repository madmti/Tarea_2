<?php
namespace SHARED;

enum AuthorizationLevel: int {
    case ADMINISTRADOR = 3;         // Jefe del comité
    case REVISOR = 2;               // Miembro del comité
    case CONTACTO = 1;              // Autor de contacto
    case GUEST = 0;                 // Invitado
}
enum Method: string {
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case ALL = '.*';
}

class AuthLevelRoute {
    public string $path;
    public array $methods;
    public AuthorizationLevel $authLevel;

    public function __construct(string $path, array $methods, AuthorizationLevel $authLevel) {
        $this->path = $path;
        $this->methods = $methods;
        $this->authLevel = $authLevel;
    }

    public function matches(string $path, Method $method): bool {
        $cond_1 = preg_match("#{$this->path}#", $path);
        $cond_2 = in_array($method, $this->methods) || in_array(Method::ALL, $this->methods);
        return $cond_1 && $cond_2;
    }
}

$routes = [
    new AuthLevelRoute('/private/.*',                       [Method::ALL], AuthorizationLevel::ADMINISTRADOR),
    new AuthLevelRoute('/protected/mis_articulos',          [Method::ALL], AuthorizationLevel::CONTACTO),
];

function getAuthorizationLevel(string $path, Method $method): ?AuthorizationLevel {
    global $routes;
    foreach ($routes as $route) {
        if ($route->matches($path, $method)) {
            return $route->authLevel;
        }
    }
    return AuthorizationLevel::GUEST;
}