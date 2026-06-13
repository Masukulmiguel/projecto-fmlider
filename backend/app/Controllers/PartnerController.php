<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;

class PartnerController
{
    public function index()
    {
        $db = Database::connection();
        $result = $db->query("SELECT * FROM partners WHERE status = 1 ORDER BY order_by ASC, id ASC");
        $partners = [];
        while ($row = $result->fetch_assoc()) {
            $partners[] = $row;
        }
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'partners' => $partners], JSON_UNESCAPED_UNICODE);
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['name'])) {
            Response::error('Nome é obrigatório', 422);
        }

        $db = Database::connection();
        $stmt = $db->prepare("INSERT INTO partners (name, logo, website, description, order_by, status) VALUES (?, ?, ?, ?, ?, ?)");
        $logo = $data['logo'] ?? '';
        $website = $data['website'] ?? '';
        $desc = $data['description'] ?? '';
        $order = $data['order_by'] ?? 0;
        $status = $data['status'] ?? 1;
        $stmt->bind_param('ssssii', $data['name'], $logo, $website, $desc, $order, $status);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Parceiro criado', 'id' => $id], JSON_UNESCAPED_UNICODE);
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $db = Database::connection();

        $stmt = $db->prepare("UPDATE partners SET name=?, logo=?, website=?, description=?, order_by=?, status=? WHERE id=?");
        $name = $data['name'] ?? '';
        $logo = $data['logo'] ?? '';
        $website = $data['website'] ?? '';
        $desc = $data['description'] ?? '';
        $order = $data['order_by'] ?? 0;
        $status = $data['status'] ?? 1;
        $stmt->bind_param('ssssiii', $name, $logo, $website, $desc, $order, $status, $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Parceiro atualizado'], JSON_UNESCAPED_UNICODE);
    }

    public function destroy($id)
    {
        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM partners WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Parceiro eliminado'], JSON_UNESCAPED_UNICODE);
    }
}
