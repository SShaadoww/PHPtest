<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    public function __construct(private string $secret) {}

    public function requireUserId(): int
    {
        header('Content-Type: application/json');

        $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.+)/i', $hdr, $m)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Missing Authorization header']);
            exit;
        }
        $token = $m[1];

        try {
            $payload = JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (\Throwable $e) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid token']);
            exit;
        }

        if (!isset($payload->sub)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid token payload']);
            exit;
        }

        return (int)$payload->sub;
    }
}