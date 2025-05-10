<?php
namespace SHARED;

enum AuthorizationLevel: int {
    case ADMINISTRADOR = 3;
    case REVISOR = 2;
    case CONTACTO = 1;
    case GUEST = 0;
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
    public array $authLevels;

    public function __construct(string $path, array $methods, array $authLevels) {
        $this->path = $path;
        $this->methods = $methods;
        $this->authLevels = $authLevels;
    }

    public function matches(string $path, Method $method): bool {
        $cond_1 = preg_match("#{$this->path}#", $path);
        $cond_2 = in_array($method, $this->methods) || in_array(Method::ALL, $this->methods);
        return $cond_1 && $cond_2;
    }
}

function getAuthorizationLevel(string $path, Method $method, array $routes): ?array {
    foreach ($routes as $route) {
        if ($route->matches($path, $method)) {
            return $route->authLevels;
        }
    }
    return [AuthorizationLevel::GUEST];
}
