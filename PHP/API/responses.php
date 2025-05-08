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
}