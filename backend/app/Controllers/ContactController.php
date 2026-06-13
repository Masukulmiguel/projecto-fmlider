<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;

class ContactController
{
    public function index()
    {
        $db = Database::connection();
        $result = $db->query("SELECT * FROM contacts ORDER BY created_at DESC");
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'contacts' => $items], JSON_UNESCAPED_UNICODE);
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
            Response::error('Nome, email e mensagem são obrigatórios', 422);
        }

        $db = Database::connection();
        $stmt = $db->prepare("INSERT INTO contacts (name, company, phone, email, subject, message) VALUES (?, ?, ?, ?, ?, ?)");
        $company = $data['company'] ?? '';
        $phone = $data['phone'] ?? '';
        $subject = $data['subject'] ?? '';
        $stmt->bind_param('ssssss', $data['name'], $company, $phone, $data['email'], $subject, $data['message'], JSON_UNESCAPED_UNICODE);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Mensagem enviada com sucesso'], JSON_UNESCAPED_UNICODE);
    }

    public function show($id)
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM contacts WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();

        if (!$item) {
            Response::error('Contacto não encontrado', 404);
        }
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'contact' => $item], JSON_UNESCAPED_UNICODE);
    }

    public function markAsRead($id)
    {
        $db = Database::connection();
        $stmt = $db->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Marcado como lido'], JSON_UNESCAPED_UNICODE);
    }

    public function reply($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['reply_message'])) {
            Response::error('Mensagem de resposta é obrigatória', 422);
        }

        $db = Database::connection();
        $stmt = $db->prepare("UPDATE contacts SET reply_message = ?, replied_at = NOW(), is_read = 1 WHERE id = ?");
        $reply = $data['reply_message'];
        $stmt->bind_param('si', $reply, $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Resposta enviada'], JSON_UNESCAPED_UNICODE);
    }

    public function destroy($id)
    {
        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Contacto eliminado'], JSON_UNESCAPED_UNICODE);
    }
}
