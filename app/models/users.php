<?php
class User {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function findByLogin($login) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function create($login, $passwordHash) {
        $stmt = $this->pdo->prepare("INSERT INTO users 
                                            (login, 
                                             password_hash) 
                                        VALUES (?, ?)");
        $stmt->execute([$login, $passwordHash]);
        return $this->pdo->lastInsertId();
    }
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT id, login FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}