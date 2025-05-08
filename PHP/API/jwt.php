<?php
namespace API;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper {
    private static string $secret = 'CLAVE_SUPER_SECRETA';
    public static function generarToken(int $idUsuario, string $tipo): string {
        $payload = [
            'sub' => $idUsuario,
            'tipo' => $tipo,
            'iat' => time(),
            'exp' => time() + 3600  // 1H de validez
        ];
        return JWT::encode($payload, self::$secret, 'HS256');
    }

    public static function verificarToken(string $jwt): ?array {
        try {
            return (array) JWT::decode($jwt, new Key(self::$secret, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }
}
