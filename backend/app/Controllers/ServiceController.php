<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;

class ServiceController
{
    public function index()
    {
        $db = Database::connection();
        $result = $db->query("SELECT * FROM services WHERE status = 1 ORDER BY order_by ASC, id ASC");
        $services = [];
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'services' => $services], JSON_UNESCAPED_UNICODE);
    }

    public function show($id)
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $service = $result->fetch_assoc();
        $stmt->close();

        if (!$service) {
            Response::error('Serviço não encontrado', 404);
        }
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'service' => $service], JSON_UNESCAPED_UNICODE);
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['title'])) {
            Response::error('Título é obrigatório', 422);
        }

        $db = Database::connection();
        $slug = $this->slugify($data['title'], JSON_UNESCAPED_UNICODE);

        $stmt = $db->prepare("INSERT INTO services (title, slug, description, content, image, status, order_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $status = $data['status'] ?? 1;
        $order = $data['order_by'] ?? 0;
        $desc = $data['description'] ?? '';
        $content = $data['content'] ?? '';
        $image = $data['image'] ?? '';
        $stmt->bind_param('ssssssi', $data['title'], $slug, $desc, $content, $image, $status, $order);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Serviço criado', 'id' => $id], JSON_UNESCAPED_UNICODE);
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $db = Database::connection();

        $stmt = $db->prepare("UPDATE services SET title=?, slug=?, description=?, content=?, image=?, status=?, order_by=? WHERE id=?");
        $slug = !empty($data['slug']) ? $data['slug'] : (isset($data['title']) ? $this->slugify($data['title']) : '');
        $status = $data['status'] ?? 1;
        $order = $data['order_by'] ?? 0;
        $desc = $data['description'] ?? '';
        $content = $data['content'] ?? '';
        $image = $data['image'] ?? '';
        $title = $data['title'] ?? '';
        $stmt->bind_param('sssssiii', $title, $slug, $desc, $content, $image, $status, $order, $id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Serviço atualizado'], JSON_UNESCAPED_UNICODE);
    }

    public function destroy($id)
    {
        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Serviço eliminado'], JSON_UNESCAPED_UNICODE);
    }

    private function slugify($text)
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}
