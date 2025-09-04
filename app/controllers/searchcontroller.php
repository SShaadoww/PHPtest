<?php
require_once __DIR__ . '/../models/book.php';
require_once __DIR__ . '/../services/jwt.php';

class SearchController
{
    private PDO $db;
    private JwtService $jwt;
    public function __construct(PDO $db, JwtService $jwt)
    {
        $this->db = $db;
        $this->jwt = $jwt;
    }
    public function google()
    {
        $query = $_GET['q'] ?? '';
        if (!$query) {
            echo json_encode(["success" => false, "error" => "Missing search query"]);
            return;
        }

        $url = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($query);
        $response = file_get_contents($url);
        if (!$response) {
            echo json_encode(["success" => false, "error" => "Failed to fetch data"]);
            return;
        }

        $data = json_decode($response, true);
        $books = [];

        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $books[] = [
                    "id" => $item['id'] ?? null,
                    "title" => $item['volumeInfo']['title'] ?? 'No title',
                    "description" => $item['volumeInfo']['description'] ?? '',
                ];
            }
        }

        echo json_encode(["success" => true, "books" => $books]);
    }
    public function mif()
    {
        $query = $_GET['q'] ?? '';
        if (!$query) {
            echo json_encode(["success" => false, "error" => "Missing search query"]);
            return;
        }

        $url = "https://www.mann-ivanov-ferber.ru/book/search.ajax?q=" . urlencode($query);
        $response = file_get_contents($url);
        if (!$response) {
            echo json_encode(["success" => false, "error" => "Failed to fetch data"]);
            return;
        }

        $data = json_decode($response, true);
        $books = [];

        if (!empty($data)) {
            foreach ($data as $item) {
                $books[] = [
                    "title" => $item['title'] ?? 'No title',
                    "url" => $item['url'] ?? '',
                ];
            }
        }
        echo json_encode(["success" => true, "books" => $books]);
    }
    public function saveExternal()
    {
        $userId = $this->jwt->requireUserId();
        if (!$userId) {
            echo json_encode(["success" => false, "error" => "Unauthorized"]);
            return;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || empty($data['title']) || empty($data['content'])) {
            echo json_encode(["success" => false, "error" => "Missing fields"]);
            return;
        }
        $book = new Book($this->db);
        $book->createExternal($userId, $data['title'], $data['content']);
        echo json_encode(["success" => true, "message" => "Book saved"]);
    }
}