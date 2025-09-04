<?php
require_once __DIR__ . '/../models/users.php';
require_once __DIR__ . '/../models/permission.php';

class UserController
{
    private PDO $db;
    private User $userModel;
    private Permission $permissionModel;
    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->userModel = new User($db);
        $this->permissionModel = new Permission($db);
    }
    public function list()
    {
        $users = $this->userModel->getAll();
        header('Content-Type: application/json');
        echo json_encode($users);
    }
    public function grantAccess()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['owner_id'], $input['shared_with_user_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'owner_id and shared_with_user_id are required']);
            return;
        }
        $success = $this->permissionModel->grantAccess((int)$input['owner_id'], (int)$input['shared_with_user_id']);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Access granted']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to grant access']);
        }
    }
}