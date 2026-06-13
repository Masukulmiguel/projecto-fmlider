<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;

class FAQController
{
    public function index()
    {
        $db = Database::connection();
        $result = $db->query("SELECT * FROM faqs WHERE status = 1 ORDER BY order_by ASC, id ASC");
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'faqs' => $items], JSON_UNESCAPED_UNICODE);
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['question']) || empty($data['answer'])) {
            Response::error('Pergunta e resposta são obrigatórias', 422);
        }

        $db = Database::connection();
        $stmt = $db->prepare("INSERT INTO faqs (question, answer, category, order_by, status) VALUES (?, ?, ?, ?, ?)");
        $category = $data['category'] ?? '';
        $order = $data['order_by'] ?? 0;
        $status = $data['status'] ?? 1;
        $stmt->bind_param('sssii', $data['question'], $data['answer'], $category, $order, $status);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'FAQ criado', 'id' => $id], JSON_UNESCAPED_UNICODE);
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $db = Database::connection();

        $stmt = $db->prepare("UPDATE faqs SET question=?, answer=?, category=?, order_by=?, status=? WHERE id=?");
        $question = $data['question'] ?? '';
        $answer = $data['answer'] ?? '';
        $category = $data['category'] ?? '';
        $order = $data['order_by'] ?? 0;
        $status = $data['status'] ?? 1;
        $stmt->bind_param('sssiii', $question, $answer, $category, $order, $status, $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'FAQ atualizado'], JSON_UNESCAPED_UNICODE);
    }

    public function destroy($id)
    {
        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM faqs WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'FAQ eliminado'], JSON_UNESCAPED_UNICODE);
    }
}
