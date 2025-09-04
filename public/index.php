<?php
require_once __DIR__ . '/../app/controllers/usercontroller.php';
require_once __DIR__ . '/../app/controllers/authcontroller.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/bookcontroller.php';
require_once __DIR__ . '/../app/services/jwt.php';
require_once __DIR__ . '/../app/controllers/searchcontroller.php';
$config = require __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO($config['db']['dsn'], $config['db']['user'], $config['db']['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

$authController = new AuthController($pdo);
$userController = new UserController($pdo);
$jwt = new JwtService($config['jwt_secret']);
$book = new BookController($pdo, $jwt);
$search = new SearchController($pdo, $jwt);


if ($path === '/api/register' && $method === 'POST') {
    $authController->register($data);
} elseif ($path === '/api/login' && $method === 'POST') {
    $authController->login($data);
}

elseif ($path === '/api/users' && $method === 'GET') {
    $userController->list();
} elseif ($path === '/api/users/grant' && $method === 'POST') {
    $userController->grantAccess();
}

if ($path === '/api/books' && $method === 'GET') { $book->list(); exit; }
if ($path === '/api/books' && $method === 'POST') { $book->create(); exit; }

if (preg_match('#^/api/books/(\d+)$#', $path, $m)) {
    $id = (int)$m[1];
    if ($method === 'GET')   { $book->open($id); exit; }
    if ($method === 'PUT')   { $book->update($id); exit; }
    if ($method === 'DELETE'){ $book->delete($id); exit; }
}

if (preg_match('#^/api/books/(\d+)/restore$#', $path, $m) && $method === 'PATCH') {
    $book->restore((int)$m[1]); exit;
}

if (preg_match('#^/api/books/user/(\d+)$#', $path, $m) && $method === 'GET') {
    $book->listOtherUser((int)$m[1]);
    exit;
}

if ($path === '/api/search/google' && $method === 'GET') { $search->google(); exit; }
if ($path === '/api/search/mif' && $method === 'GET')    { $search->mif(); exit; }
if ($path === '/api/books/save-external' && $method === 'POST') { $search->saveExternal(); exit; }

else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}

