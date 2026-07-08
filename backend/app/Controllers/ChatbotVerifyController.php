<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;

class ChatbotVerifyController
{
    public function verifyClient()
    {
        $data = Response::input();
        $email = trim($data['email'] ?? '');
        $username = trim($data['username'] ?? '');

        if ($email === '' || $username === '') {
            Response::error('Email e username são obrigatórios.', 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Email inválido.', 422);
        }

        $db = Database::connection();

        $stmt = $db->prepare("SELECT id, username, name, email, role, approval_status, photo FROM users WHERE email = ? AND username = ? AND role = 'cliente' LIMIT 1");
        if (!$stmt) {
            Response::error('Erro interno.', 500);
        }

        $stmt->bind_param('ss', $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            Response::error('Cliente não encontrado. Verifique o email e username.', 404);
        }

        if ($user['approval_status'] !== 'approved') {
            Response::error('Conta ainda não aprovada. Contacte o administrador.', 403);
        }

        $company = null;
        $stmt2 = $db->prepare("SELECT company_name, nif, phone, email, address FROM companies WHERE user_id = ? LIMIT 1");
        if ($stmt2) {
            $stmt2->bind_param('i', $user['id']);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $company = $result2->fetch_assoc();
            $stmt2->close();
        }

        Response::success([
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'photo' => $user['photo'],
            ],
            'company' => $company,
        ]);
    }
}
