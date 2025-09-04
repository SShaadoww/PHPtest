<?php
class Book
{
    public function getDb(): PDO
    {
        return $this->db;
    }
    public function __construct(private PDO $db) {}
    public function getByUser(int $userId): array
    {
        $sql = "SELECT 
                    id, 
                    title 
                FROM books 
                WHERE 
                    user_id = ? 
                    AND deleted_at IS NULL ORDER BY id DESC";
        $st = $this->db->prepare($sql);
        $st->execute([$userId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public function create(int $userId, string $title, string $content): int
    {
        $st = $this->db->prepare("INSERT INTO books 
                                            (user_id, 
                                             title, 
                                             content) 
                                        VALUES (?, ?, ?)");
        $st->execute([$userId, $title, $content]);
        return (int)$this->db->lastInsertId();
    }
    public function getById(int $id, int $userId): ?array
    {
        $st = $this->db->prepare("SELECT 
                                            id, 
                                            title, 
                                            content 
                                        FROM books 
                                        WHERE 
                                            id = ? 
                                            AND user_id = ? 
                                            AND deleted_at IS NULL");
        $st->execute([$id, $userId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
    public function update(int $id, int $userId, string $title, string $content): bool
    {
        $st = $this->db->prepare("UPDATE books 
                                        SET 
                                            title = ?, 
                                            content = ? 
                                        WHERE 
                                            id = ? 
                                            AND user_id = ? 
                                            AND deleted_at IS NULL");
        return $st->execute([$title, $content, $id, $userId]);
    }
    public function softDelete(int $id, int $userId): bool
    {
        $st = $this->db->prepare("UPDATE books 
                                        SET deleted_at = NOW() 
                                        WHERE 
                                            id = ? 
                                            AND user_id = ? 
                                            AND deleted_at IS NULL");
        return $st->execute([$id, $userId]);
    }
    public function restore(int $id, int $userId): bool
    {
        $st = $this->db->prepare("UPDATE books 
                                        SET deleted_at = NULL 
                                        WHERE 
                                            id = ? 
                                            AND user_id = ? 
                                            AND deleted_at IS NOT NULL");
        return $st->execute([$id, $userId]);
    }
    public function createExternal(int $userId, string $title, string $content): bool
    {
        $stmt = $this->db->prepare("INSERT INTO books 
                                                (user_id, 
                                                 title, 
                                                 content) 
                                            VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $title, $content]);
    }
}