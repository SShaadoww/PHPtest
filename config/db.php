<?php
$config = require __DIR__ . '/config.php';

$dsn  = $config['db']['dsn'];
$user = $config['db']['user'];
$pass = $config['db']['pass'];

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}