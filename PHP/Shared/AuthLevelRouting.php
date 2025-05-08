<?php
namespace SHARED;


enum AuthorizationLevel: int {
    case ADMINISTRADOR = 3;         // Jefe del comité
    case REVISOR = 2;               // Miembro del comité
    case CONTACTO = 1;              // Autor de contacto
    case GUEST = 0;                 // Invitado
}

$routes = [
    '/api/public/.*' => AuthorizationLevel::GUEST,
    '/api/private/.*' => AuthorizationLevel::ADMINISTRADOR,
    '/api/protected/articulos/[0-9]+' => AuthorizationLevel::CONTACTO,
];
function getRouteAuthorizationLevel(string $path): AuthorizationLevel {
    global $routes;

    foreach ($routes as $route => $authLevel) {
        $pattern = '#^' . preg_replace('#\{[^\}]+\}#', '[^/]+', $route) . '$#';
        if (preg_match($pattern, $path)) {
            return $authLevel;
        }
    }

    return AuthorizationLevel::GUEST;
}
