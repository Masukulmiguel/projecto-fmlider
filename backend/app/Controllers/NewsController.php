<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;

class NewsController
{
    public function adminIndex()
    {
        $db = Database::connection();
        $result = $db->query("SELECT * FROM news ORDER BY published_at DESC, id DESC");
        $news = [];
        while ($row = $result->fetch_assoc()) {
            $news[] = $row;
        }
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'news' => $news], JSON_UNESCAPED_UNICODE);
    }

    public function index()
    {
        $db = Database::connection();
        $status = $_GET['status'] ?? null;
        if ($status) {
            $stmt = $db->prepare("SELECT * FROM news WHERE status = ? ORDER BY published_at DESC, id DESC");
            $stmt->bind_param('s', $status);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $db->query("SELECT * FROM news WHERE status = 'published' ORDER BY published_at DESC, id DESC");
        }
        $news = [];
        while ($row = $result->fetch_assoc()) {
            $news[] = $row;
        }
        if (isset($stmt)) $stmt->close();
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'news' => $news], JSON_UNESCAPED_UNICODE);
    }

    public function show($id)
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM news WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();

        if (!$item) {
            Response::error('Notícia não encontrada', 404);
        }
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'news' => $item], JSON_UNESCAPED_UNICODE);
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['title'])) {
            Response::error('Título é obrigatório', 422);
        }

        $db = Database::connection();
        $slug = $this->slugify($data['title']);

        $stmt = $db->prepare("INSERT INTO news (title, slug, image, description, content, category, status, user_id, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $image = $data['image'] ?? '';
        $desc = $data['description'] ?? '';
        $content = $data['content'] ?? '';
        $category = $data['category'] ?? '';
        $status = $data['status'] ?? 'draft';
        $userId = $data['user_id'] ?? null;
        $publishedAt = ($status === 'published') ? ($data['published_at'] ?? date('Y-m-d H:i:s')) : null;
        $stmt->bind_param('sssssssss', $data['title'], $slug, $image, $desc, $content, $category, $status, $userId, $publishedAt);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Notícia criada', 'id' => $id], JSON_UNESCAPED_UNICODE);
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $db = Database::connection();

        $stmt = $db->prepare("UPDATE news SET title=?, slug=?, image=?, description=?, content=?, category=?, status=?, published_at=? WHERE id=?");
        $slug = !empty($data['slug']) ? $data['slug'] : (isset($data['title']) ? $this->slugify($data['title']) : '');
        $image = $data['image'] ?? '';
        $desc = $data['description'] ?? '';
        $content = $data['content'] ?? '';
        $category = $data['category'] ?? '';
        $status = $data['status'] ?? 'draft';
        $publishedAt = $data['published_at'] ?? null;
        $title = $data['title'] ?? '';
        $stmt->bind_param('ssssssssi', $title, $slug, $image, $desc, $content, $category, $status, $publishedAt, $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Notícia atualizada'], JSON_UNESCAPED_UNICODE);
    }

    public function destroy($id)
    {
        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM news WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Notícia eliminada'], JSON_UNESCAPED_UNICODE);
    }

    private function slugify($text)
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}
