<?php
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../services/jwt.php';

class BookController
{
    private Book $books;
    private JwtService $jwt;

    public function __construct(PDO $db, JwtService $jwt)
    {
        $this->books = new Book($db);
        $this->jwt = $jwt;
    }
    public function list(): void
    {
        header('Content-Type: application/json');
        $userId = $this->jwt->requireUserId();
        echo json_encode($this->books->getByUser($userId));
    }
    public function create(): void
    {
        header('Content-Type: application/json');
        $userId = $this->jwt->requireUserId();

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $title = '';
        $content = '';

        if (str_starts_with($contentType, 'application/json')) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $title = trim($input['title'] ?? '');
            $content = (string)($input['content'] ?? '');
        } elseif (str_starts_with($contentType, 'multipart/form-data')) {
            $title = trim($_POST['title'] ?? '');
            if (!empty($_FILES['file']['tmp_name'])) {
                // читаем текстовый файл
                $content = @file_get_contents($_FILES['file']['tmp_name']) ?: '';
            } else {
                $content = (string)($_POST['content'] ?? '');
            }
        } else {
            http_response_code(415);
            echo json_encode(['success' => false, 'error' => 'Unsupported Content-Type']);
            return;
        }

        if ($title === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Title is required']);
            return;
        }

        $bookId = $this->books->create($userId, $title, $content);
        http_response_code(201);
        echo json_encode(['success' => true, 'id' => $bookId]);
    }
    public function open(int $id): void
    {
        header('Content-Type: application/json');
        $userId = $this->jwt->requireUserId();
        $book = $this->books->getById($id, $userId);
        if (!$book) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Book not found']);
            return;
        }
        echo json_encode($book);
    }
    public function update(int $id): void
    {
        header('Content-Type: application/json');
        $userId = $this->jwt->requireUserId();

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $title = trim($input['title'] ?? '');
        $content = (string)($input['content'] ?? '');

        if ($title === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Title is required']);
            return;
        }

        $ok = $this->books->update($id, $userId, $title, $content);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Book not found or deleted']);
            return;
        }
        echo json_encode(['success' => true]);
    }
    public function delete(int $id): void
    {
        header('Content-Type: application/json');
        $userId = $this->jwt->requireUserId();
        $ok = $this->books->softDelete($id, $userId);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Book not found or already deleted']);
            return;
        }
        echo json_encode(['success' => true]);
    }
    public function restore(int $id): void
    {
        header('Content-Type: application/json');
        $userId = $this->jwt->requireUserId();
        $ok = $this->books->restore($id, $userId);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Nothing to restore']);
            return;
        }
        echo json_encode(['success' => true]);
    }
    public function listOtherUser(int $ownerId): void
    {
        header('Content-Type: application/json');
        $userId = $this->jwt->requireUserId();

        $permission = new Permission($this->books->getDb());
        if (!$permission->hasAccess($ownerId, $userId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            return;
        }

        $books = $this->books->getByUser($ownerId);
        echo json_encode($books);
    }
}