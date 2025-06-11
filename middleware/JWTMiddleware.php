<?php

require_once 'vendor/autoload.php';
require_once 'config/jwt.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTMiddleware {

    public static function generateToken($userId, $userEmail) {
        try {
            $issuedAt = time();
            $expirationTime = $issuedAt + JWTConfig::$expire_time;

            $payload = array(
                'iss' => JWTConfig::$issuer,
                'aud' => JWTConfig::$audience,
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'data' => array(
                    'id' => $userId,
                    'email' => $userEmail
                )
            );

            return JWT::encode($payload, JWTConfig::$secret_key, 'HS256');

        } catch (Exception $e) {
            throw new Exception("Erro ao gerar token: " . $e->getMessage());
        }
    }

    public static function validateToken() {
        try {
            $headers = self::getAuthorizationHeader();

            if (!$headers) {
                self::sendUnauthorizedResponse("Token não fornecido");
                return false;
            }

            // Extrair token do cabeçalho Bearer
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                $token = $matches[1];
            } else {
                self::sendUnauthorizedResponse("Formato de token inválido");
                return false;
            }

            // Decodificar e validar token
            $decoded = JWT::decode($token, new Key(JWTConfig::$secret_key, 'HS256'));

            // Verificar se token não expirou
            if ($decoded->exp < time()) {
                self::sendUnauthorizedResponse("Token expirado");
                return false;
            }

            // Retornar dados do usuário
            return $decoded->data;

        } catch (Exception $e) {
            self::sendUnauthorizedResponse("Token inválido: " . $e->getMessage());
            return false;
        }
    }

    private static function getAuthorizationHeader() {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } else if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        return $headers;
    }

    private static function sendUnauthorizedResponse($message) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Acesso não autorizado',
            'error' => $message
        ]);
        exit();
    }
}
