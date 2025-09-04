<?php
class Permission
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function grantAccess(int $ownerId, int $sharedWithUserId): bool
    {
        $stmt = $this->db->prepare("INSERT INTO permissions 
                                                (owner_id, 
                                                 shared_with_user_id) 
                                            VALUES (?, ?)");
        return $stmt->execute([$ownerId, $sharedWithUserId]);
    }
    public function hasAccess(int $ownerId, int $userId): bool
    {
        if ($ownerId === $userId) return true;

        $stmt = $this->db->prepare("
            SELECT 1 
            FROM permissions 
            WHERE 
                owner_id = ? 
                AND shared_with_user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$ownerId, $userId]);
        return (bool)$stmt->fetchColumn();
    }

}