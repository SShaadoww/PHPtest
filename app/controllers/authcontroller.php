<?php
require_once __DIR__ . "/../../vendor/autoload.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $userModel;
    private $secret = "my_super_secret_key";
    public function __construct($pdo)
    {
        require_once __DIR__ . "/../models/users.php";
        $this->userModel = new User($pdo);
    }
    public function register($data)
    {
        $login = trim($data['login'] ?? '');
        $password = $data['password'] ?? '';
        $confirm = $data['confirmPassword'] ?? '';

        if (!$login || !$password || !$confirm) {
            return $this->json(['success' => false, 'error' => 'All fields required']);
        }
        if ($password !== $confirm)
        {
            return $this->json(['success' => false, 'error' => 'Passwords do not match']);
        }
        if ($this->userModel->findByLogin($login))
        {
            return $this->json(['success' => false, 'error' => 'Login already exists']);
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $userId = $this->userModel->create($login, $hash);
        return $this->json(['success' => true,'token' => $this->generateToken($userId, $login)]);
    }
    public function login($data)
    {
        $login = trim($data['login'] ?? '');
        $password = $data['password'] ?? '';
        $user = $this->userModel->findByLogin($login);

        if (!$user || !password_verify($password, $user['password_hash']))
        {
            return $this->json(['success' => false, 'error' => 'Invalid credentials']);
        }
        return $this->json(['success' => true,'token' => $this->generateToken($user['id'], $user['login'])]);
    }
    private function generateToken($id, $login) {
        $payload = [
            'sub' => $id,
            'login' => $login,
            'iat' => time(),
            'exp' => time() + 3600
        ];
        return JWT::encode($payload, $this->secret, 'HS256');
    }
    private function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}