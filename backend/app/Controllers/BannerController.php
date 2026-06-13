<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;

class BannerController
{
    public function index()
    {
        $db = Database::connection();
        $result = $db->query("SELECT * FROM banners ORDER BY order_by ASC, id DESC");
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'banners' => $items], JSON_UNESCAPED_UNICODE);
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['title'])) {
            Response::error('Título é obrigatório', 422);
        }

        $db = Database::connection();
        $stmt = $db->prepare("INSERT INTO banners (title, description, image, link, status, order_by) VALUES (?, ?, ?, ?, ?, ?)");
        $desc = $data['description'] ?? '';
        $image = $data['image'] ?? '';
        $link = $data['link'] ?? '';
        $status = $data['status'] ?? 1;
        $order = $data['order_by'] ?? 0;
        $stmt->bind_param('ssssii', $data['title'], $desc, $image, $link, $status, $order);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Banner criado', 'id' => $id], JSON_UNESCAPED_UNICODE);
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $db = Database::connection();

        $stmt = $db->prepare("UPDATE banners SET title=?, description=?, image=?, link=?, status=?, order_by=? WHERE id=?");
        $title = $data['title'] ?? '';
        $desc = $data['description'] ?? '';
        $image = $data['image'] ?? '';
        $link = $data['link'] ?? '';
        $status = $data['status'] ?? 1;
        $order = $data['order_by'] ?? 0;
        $stmt->bind_param('ssssiii', $title, $desc, $image, $link, $status, $order, $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Banner atualizado'], JSON_UNESCAPED_UNICODE);
    }

    public function destroy($id)
    {
        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Banner eliminado'], JSON_UNESCAPED_UNICODE);
    }

    public function publicIndex()
    {
        $db = Database::connection();
        $result = $db->query("SELECT id, title, description, image, link FROM banners WHERE status = 1 ORDER BY order_by ASC, id DESC");
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'banners' => $items], JSON_UNESCAPED_UNICODE);
    }
}
