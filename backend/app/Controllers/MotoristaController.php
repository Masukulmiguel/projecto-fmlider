<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\OwnerScope;
use App\Helpers\Response;

class MotoristaController
{
    private function ensureAdminOrLogistica($auth)
    {
        $role = $auth['user_metadata']['role'] ?? $auth['role'] ?? '';
        if (!in_array($role, ['admin', 'logistica'])) {
            Response::error('Acesso negado', 403);
        }
    }

    public function index()
    {
        $auth = OwnerScope::userFromToken();
        $this->ensureAdminOrLogistica($auth);
        $db = Database::connection();

        $search = $_GET['q'] ?? null;
        $estado = $_GET['estado'] ?? null;

        $sql = "SELECT * FROM motoristas WHERE 1=1";
        $params = [];
        $types = '';

        if ($estado) { $sql .= " AND estado = ?"; $params[] = $estado; $types .= 's'; }
        if ($search) {
            $sql .= " AND (nome_completo LIKE ? OR bilhete_identidade LIKE ? OR telefone LIKE ?)";
            $like = "%$search%";
            $params = array_merge($params, [$like, $like, $like]);
            $types .= 'sss';
        }

        $sql .= " ORDER BY nome_completo ASC LIMIT 500";

        $stmt = $db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        Response::success(['motoristas' => $rows]);
    }

    public function show($id)
    {
        $auth = OwnerScope::userFromToken();
        $this->ensureAdminOrLogistica($auth);
        $db = Database::connection();

        $stmt = $db->prepare("SELECT * FROM motoristas WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Motorista não encontrado', 404);

        Response::success(['motorista' => $row]);
    }

    public function store()
    {
        $auth = OwnerScope::userFromToken();
        $this->ensureAdminOrLogistica($auth);

        $data = Response::input();
        $errors = $this->validate($data);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $db = Database::connection();
        $stmt = $db->prepare("INSERT INTO motoristas (nome_completo, bilhete_identidade, telefone, email, nr_cnh, validade_cnh, nr_aptidao, validade_aptidao, empresa, estado, notas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $estado = $data['estado'] ?? 'activo';

        $stmt->bind_param('sssssssssss',
            $data['nome_completo'],
            $data['bilhete_identidade'] ?? null,
            $data['telefone'] ?? null,
            $data['email'] ?? null,
            $data['nr_cnh'] ?? null,
            $data['validade_cnh'] ?? null,
            $data['nr_aptidao'] ?? null,
            $data['validade_aptidao'] ?? null,
            $data['empresa'] ?? null,
            $estado,
            $data['notas'] ?? null
        );

        if (!$stmt->execute()) {
            error_log('Database error (MotoristaController store): ' . $stmt->error);
            Response::error('Erro interno do servidor', 500);
        }
        $newId = $stmt->insert_id;
        $stmt->close();

        Response::success(['motorista_id' => $newId], 'Motorista criado');
    }

    public function update($id)
    {
        $auth = OwnerScope::userFromToken();
        $this->ensureAdminOrLogistica($auth);
        $db = Database::connection();

        $stmt = $db->prepare('SELECT id FROM motoristas WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Motorista não encontrado', 404);

        $data = Response::input();
        $errors = $this->validate($data, true);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $stmt = $db->prepare("UPDATE motoristas SET nome_completo=?, bilhete_identidade=?, telefone=?, email=?, nr_cnh=?, validade_cnh=?, nr_aptidao=?, validade_aptidao=?, empresa=?, estado=?, notas=? WHERE id=?");
        $stmt->bind_param('sssssssssssi',
            $data['nome_completo'],
            $data['bilhete_identidade'] ?? null,
            $data['telefone'] ?? null,
            $data['email'] ?? null,
            $data['nr_cnh'] ?? null,
            $data['validade_cnh'] ?? null,
            $data['nr_aptidao'] ?? null,
            $data['validade_aptidao'] ?? null,
            $data['empresa'] ?? null,
            $data['estado'] ?? 'activo',
            $data['notas'] ?? null,
            $id
        );
        $stmt->execute();
        $stmt->close();

        Response::success([], 'Motorista atualizado');
    }

    public function destroy($id)
    {
        $auth = OwnerScope::userFromToken();
        $this->ensureAdminOrLogistica($auth);
        $db = Database::connection();

        $stmt = $db->prepare('SELECT id FROM motoristas WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Motorista não encontrado', 404);

        $stmt = $db->prepare('DELETE FROM motoristas WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        Response::success([], 'Motorista removido');
    }

    private function validate($data, $isUpdate = false)
    {
        $errors = [];
        if (empty($data['nome_completo'])) $errors['nome_completo'] = 'Nome completo é obrigatório';
        return $errors;
    }
}
