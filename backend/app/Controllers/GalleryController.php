<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;

class GalleryController
{
    public function index()
    {
        $db = Database::connection();
        $category = $_GET['category'] ?? null;
        if ($category) {
            $stmt = $db->prepare("SELECT * FROM gallery WHERE category = ? ORDER BY order_by ASC, id DESC");
            $stmt->bind_param('s', $category);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $db->query("SELECT * FROM gallery ORDER BY order_by ASC, id DESC");
        }
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        if (isset($stmt)) $stmt->close();
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'gallery' => $items], JSON_UNESCAPED_UNICODE);
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['image'])) {
            Response::error('Imagem é obrigatória', 422);
        }

        $db = Database::connection();
        $stmt = $db->prepare("INSERT INTO gallery (title, image, category, description, alt_text, order_by, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $title = $data['title'] ?? '';
        $category = $data['category'] ?? '';
        $desc = $data['description'] ?? '';
        $alt = $data['alt_text'] ?? '';
        $order = $data['order_by'] ?? 0;
        $userId = $data['user_id'] ?? null;
        $stmt->bind_param('sssssii', $title, $data['image'], $category, $desc, $alt, $order, $userId);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Imagem adicionada', 'id' => $id], JSON_UNESCAPED_UNICODE);
    }

    public function destroy($id)
    {
        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Imagem eliminada'], JSON_UNESCAPED_UNICODE);
    }

    public function upload()
    {
        if (!isset($_FILES['image'])) {
            Response::error('Nenhum ficheiro enviado', 422);
        }

        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::error('Erro no upload', 422);
        }

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if (!isset($allowed[$file['type']])) {
            Response::error('Formato inválido', 422);
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            Response::error('Ficheiro demasiado grande (máx 10MB)', 422);
        }

        $ext = $allowed[$file['type']];
        $name = 'gallery_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $dir = BASE_PATH . '/storage/uploads/gallery';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $dest = $dir . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            Response::error('Falha ao guardar o ficheiro', 500);
        }

        $publicPath = '/backend/storage/uploads/gallery/' . $name;
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'path' => $publicPath], JSON_UNESCAPED_UNICODE);
    }
}
