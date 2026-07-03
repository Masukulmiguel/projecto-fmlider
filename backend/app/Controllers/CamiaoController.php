<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\OwnerScope;
use App\Helpers\Response;

class CamiaoController
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

        $sql = "SELECT * FROM camioes WHERE 1=1";
        $params = [];
        $types = '';

        if ($estado) { $sql .= " AND estado = ?"; $params[] = $estado; $types .= 's'; }
        if ($search) {
            $sql .= " AND (codigo_interno LIKE ? OR matricula LIKE ? OR marca LIKE ? OR modelo LIKE ?)";
            $like = "%$search%";
            $params = array_merge($params, [$like, $like, $like, $like]);
            $types .= 'ssss';
        }

        $sql .= " ORDER BY codigo_interno ASC, matricula ASC LIMIT 500";

        $stmt = $db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        Response::success(['camioes' => $rows]);
    }

    public function show($id)
    {
        $auth = OwnerScope::userFromToken();
        $this->ensureAdminOrLogistica($auth);
        $db = Database::connection();

        $stmt = $db->prepare("SELECT * FROM camioes WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Camiao não encontrado', 404);

        Response::success(['camiao' => $row]);
    }

    public function store()
    {
        $auth = OwnerScope::userFromToken();
        $this->ensureAdminOrLogistica($auth);

        $data = Response::input();
        $errors = $this->validate($data);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $db = Database::connection();
        $stmt = $db->prepare("INSERT INTO camioes (codigo_interno, matricula, marca, modelo, ano, cor, cap_carga_kg, cap_carga_m3, estado, notas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $estado = $data['estado'] ?? 'activo';
        $ano = !empty($data['ano']) ? (int)$data['ano'] : null;
        $capKg = !empty($data['cap_carga_kg']) ? (float)$data['cap_carga_kg'] : null;
        $capM3 = !empty($data['cap_carga_m3']) ? (float)$data['cap_carga_m3'] : null;

        $stmt->bind_param('ssssiiidss',
            $data['codigo_interno'] ?? null,
            $data['matricula'],
            $data['marca'] ?? null,
            $data['modelo'] ?? null,
            $ano,
            $data['cor'] ?? null,
            $capKg,
            $capM3,
            $estado,
            $data['notas'] ?? null
        );

        if (!$stmt->execute()) {
            error_log('Database error (CamiaoController store): ' . $stmt->error);
            Response::error('Erro interno do servidor', 500);
        }
        $newId = $stmt->insert_id;
        $stmt->close();

        Response::success(['camiao_id' => $newId], 'Camiao criado');
    }

    public function update($id)
    {
        $auth = OwnerScope::userFromToken();
        $this->ensureAdminOrLogistica($auth);
        $db = Database::connection();

        $stmt = $db->prepare('SELECT id FROM camioes WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Camiao não encontrado', 404);

        $data = Response::input();
        $errors = $this->validate($data, true);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $ano = !empty($data['ano']) ? (int)$data['ano'] : null;
        $capKg = !empty($data['cap_carga_kg']) ? (float)$data['cap_carga_kg'] : null;
        $capM3 = !empty($data['cap_carga_m3']) ? (float)$data['cap_carga_m3'] : null;

        $stmt = $db->prepare("UPDATE camioes SET codigo_interno=?, matricula=?, marca=?, modelo=?, ano=?, cor=?, cap_carga_kg=?, cap_carga_m3=?, estado=?, notas=? WHERE id=?");
        $stmt->bind_param('ssssiiidssi',
            $data['codigo_interno'] ?? null,
            $data['matricula'],
            $data['marca'] ?? null,
            $data['modelo'] ?? null,
            $ano,
            $data['cor'] ?? null,
            $capKg,
            $capM3,
            $data['estado'] ?? 'activo',
            $data['notas'] ?? null,
            $id
        );
        $stmt->execute();
        $stmt->close();

        Response::success([], 'Camiao atualizado');
    }

    public function destroy($id)
    {
        $auth = OwnerScope::userFromToken();
        $this->ensureAdminOrLogistica($auth);
        $db = Database::connection();

        $stmt = $db->prepare('SELECT id FROM camioes WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Camiao não encontrado', 404);

        $stmt = $db->prepare('DELETE FROM camioes WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        Response::success([], 'Camiao removido');
    }

    private function validate($data, $isUpdate = false)
    {
        $errors = [];
        if (empty($data['matricula'])) $errors['matricula'] = 'Matrícula é obrigatória';
        return $errors;
    }
}
