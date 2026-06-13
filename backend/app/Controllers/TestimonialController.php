<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;

class TestimonialController
{
    public function index()
    {
        $db = Database::connection();
        $result = $db->query("SELECT * FROM testimonials WHERE status = 1 ORDER BY order_by ASC, id DESC");
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'testimonials' => $items], JSON_UNESCAPED_UNICODE);
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['name']) || empty($data['message'])) {
            Response::error('Nome e mensagem são obrigatórios', 422);
        }

        $db = Database::connection();
        $stmt = $db->prepare("INSERT INTO testimonials (name, position, company, message, photo, rating, status, order_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $position = $data['position'] ?? '';
        $company = $data['company'] ?? '';
        $photo = $data['photo'] ?? '';
        $rating = $data['rating'] ?? 5;
        $status = $data['status'] ?? 1;
        $order = $data['order_by'] ?? 0;
        $stmt->bind_param('sssssiii', $data['name'], $position, $company, $data['message'], $photo, $rating, $status, $order);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Testemunho criado', 'id' => $id], JSON_UNESCAPED_UNICODE);
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $db = Database::connection();

        $stmt = $db->prepare("UPDATE testimonials SET name=?, position=?, company=?, message=?, photo=?, rating=?, status=?, order_by=? WHERE id=?");
        $name = $data['name'] ?? '';
        $position = $data['position'] ?? '';
        $company = $data['company'] ?? '';
        $message = $data['message'] ?? '';
        $photo = $data['photo'] ?? '';
        $rating = $data['rating'] ?? 5;
        $status = $data['status'] ?? 1;
        $order = $data['order_by'] ?? 0;
        $stmt->bind_param('sssssiiii', $name, $position, $company, $message, $photo, $rating, $status, $order, $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Testemunho atualizado'], JSON_UNESCAPED_UNICODE);
    }

    public function destroy($id)
    {
        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Testemunho eliminado'], JSON_UNESCAPED_UNICODE);
    }
}
