<?php

namespace API;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;

class ResponseHelper {
    public static function json(Response $response, array $data, int $statusCode = 200): Response {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    public static function error(Response $response, string $message, int $statusCode = 400): Response {
        $data = ['error' => $message];
        return self::json($response, $data, $statusCode);
    }

    public static function success(Response $response, string $message, array $extraData = []): Response {
        $data = array_merge(['success' => $message], $extraData);
        return self::json($response, $data, 200);
    }

    public static function redirect(Response $response, string $url, int $statusCode = 302): Response {
        return $response
            ->withHeader('Location', $url)
            ->withStatus($statusCode);
    }

    public static function r_error(string $message, int $status = 400): Response {
        $factory = new ResponseFactory();
        $response = $factory->createResponse();
        return self::error($response, $message, $status);
    }

    public static function setTokenCookie(Response $response, string $token, string $name = 'token', int $lifetimeSeconds = 3600): Response {
        $expires = gmdate('D, d-M-Y H:i:s T', time() + $lifetimeSeconds);

        $cookie = sprintf(
            '%s=%s; Path=/; Expires=%s; HttpOnly; SameSite=Lax',
            $name,
            urlencode('Bearer '.$token),
            $expires
        );

        return $response->withHeader('Set-Cookie', $cookie);
    }

    public static function deleteTokenCookie(Response $response, string $name = 'token'): Response {
        $cookie = sprintf(
            '%s=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT; HttpOnly; SameSite=Lax',
            $name
        );

        return $response->withHeader('Set-Cookie', $cookie);
    }
}
