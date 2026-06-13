<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\OwnerScope;
use App\Helpers\Response;

class ContactoController
{
    public function index()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $search = $_GET['q'] ?? null;

        $sql = "SELECT c.*, u.name as client_name, comp.company_name FROM contactos c
                LEFT JOIN users u ON u.id = c.user_id
                LEFT JOIN companies comp ON comp.user_id = c.user_id
                WHERE 1=1";
        $params = [];
        $types = '';

        if (!OwnerScope::isAdmin($auth)) {
            $sql .= " AND c.user_id = ?";
            $params[] = $auth['user_id'];
            $types .= 'i';
        } elseif (!empty($_GET['user_id'])) {
            $sql .= " AND c.user_id = ?";
            $params[] = (int)$_GET['user_id'];
            $types .= 'i';
        }

        if ($search) { $sql .= " AND (c.name LIKE ? OR c.email LIKE ? OR c.company LIKE ? OR c.phone LIKE ?)"; $like = "%$search%"; $params = array_merge($params, [$like, $like, $like, $like]); $types .= 'ssss'; }

        $sql .= " ORDER BY c.name ASC LIMIT 500";

        $stmt = $db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        Response::success(['contactos' => $rows]);
    }

    public function show($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM contactos WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) Response::error('Contacto não encontrado', 404);
        OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);
        Response::success(['contacto' => $row]);
    }

    public function store()
    {
        $auth = OwnerScope::userFromToken();
        if (OwnerScope::isAdmin($auth)) Response::error('Admin não cria contactos', 403);

        $data = Response::input();
        $errors = $this->validate($data);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO contactos (user_id, name, company, email, phone, position, address, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssssss',
            $auth['user_id'],
            $data['name'],
            $data['company'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['position'] ?? null,
            $data['address'] ?? null,
            $data['notes'] ?? null
        );
        if (!$stmt->execute()) Response::error('Erro: ' . $stmt->error, 500);
        $newId = $stmt->insert_id;
        $stmt->close();
        Response::success(['contacto_id' => $newId], 'Contacto criado');
    }

    public function update($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT user_id FROM contactos WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) Response::error('Contacto não encontrado', 404);
        OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);

        $data = Response::input();
        $errors = $this->validate($data);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $stmt = $db->prepare('UPDATE contactos SET name=?, company=?, email=?, phone=?, position=?, address=?, notes=? WHERE id=?');
        $stmt->bind_param('sssssssi',
            $data['name'],
            $data['company'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['position'] ?? null,
            $data['address'] ?? null,
            $data['notes'] ?? null,
            $id
        );
        $stmt->execute();
        $stmt->close();
        Response::success([], 'Contacto atualizado');
    }

    public function destroy($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT user_id FROM contactos WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) Response::error('Contacto não encontrado', 404);
        OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);

        $stmt = $db->prepare('DELETE FROM contactos WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        Response::success([], 'Contacto removido');
    }

    private function validate($data)
    {
        $errors = [];
        if (empty($data['name'])) $errors['name'] = 'Nome é obrigatório';
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email inválido';
        }
        return $errors;
    }
}
