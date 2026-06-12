<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\OwnerScope;
use App\Helpers\Response;

class CotacaoController
{
    public function index()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $status = $_GET['status'] ?? null;
        $search = $_GET['q'] ?? null;

        $sql = "SELECT c.*, u.name as client_name, comp.company_name FROM cotacoes c
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

        if ($status) { $sql .= " AND c.status = ?"; $params[] = $status; $types .= 's'; }
        if ($search) { $sql .= " AND (c.reference LIKE ? OR c.origin LIKE ? OR c.destination LIKE ?)"; $like = "%$search%"; $params = array_merge($params, [$like, $like, $like]); $types .= 'sss'; }

        $sql .= " ORDER BY c.created_at DESC LIMIT 500";

        $stmt = $db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        Response::success(['cotacoes' => $rows]);
    }

    public function show($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM cotacoes WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) Response::error('Cotação não encontrada', 404);
        OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);
        Response::success(['cotacao' => $row]);
    }

    public function store()
    {
        $auth = OwnerScope::userFromToken();
        if (OwnerScope::isAdmin($auth)) Response::error('Admin não cria cotações', 403);

        $data = Response::input();
        $errors = $this->validate($data);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $reference = OwnerScope::generateReference('COT', 'cotacoes', 'reference');
        $validUntil = !empty($data['valid_until']) ? $data['valid_until'] : null;

        $db = Database::connection();
        $stmt = $db->prepare("INSERT INTO cotacoes (user_id, reference, origin, destination, type, weight, description, status, estimated_value, currency, valid_until, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param('issssdssdsss',
            $auth['user_id'],
            $reference,
            $data['origin'],
            $data['destination'],
            $data['type'] ?? 'maritimo',
            (float)($data['weight'] ?? 0),
            $data['description'] ?? null,
            $data['status'] ?? 'pendente',
            (float)($data['estimated_value'] ?? 0),
            $data['currency'] ?? 'AOA',
            $validUntil,
            $data['notes'] ?? null
        );

        if (!$stmt->execute()) Response::error('Erro: ' . $stmt->error, 500);
        $newId = $stmt->insert_id;
        $stmt->close();
        Response::success(['cotacao_id' => $newId, 'reference' => $reference], 'Cotação criada');
    }

    public function update($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT user_id FROM cotacoes WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) Response::error('Cotação não encontrada', 404);
        OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);

        $data = Response::input();
        $errors = $this->validate($data, true);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $validUntil = !empty($data['valid_until']) ? $data['valid_until'] : null;

        $stmt = $db->prepare("UPDATE cotacoes SET origin=?, destination=?, type=?, weight=?, description=?, status=?, estimated_value=?, currency=?, valid_until=?, notes=? WHERE id=?");
        $stmt->bind_param('sssdssdsssi',
            $data['origin'],
            $data['destination'],
            $data['type'] ?? 'maritimo',
            (float)($data['weight'] ?? 0),
            $data['description'] ?? null,
            $data['status'] ?? 'pendente',
            (float)($data['estimated_value'] ?? 0),
            $data['currency'] ?? 'AOA',
            $validUntil,
            $data['notes'] ?? null,
            $id
        );
        $stmt->execute();
        $stmt->close();
        Response::success([], 'Cotação atualizada');
    }

    public function destroy($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT user_id FROM cotacoes WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) Response::error('Cotação não encontrada', 404);
        OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);

        $stmt = $db->prepare('DELETE FROM cotacoes WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        Response::success([], 'Cotação removida');
    }

    public function stats()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $uid = OwnerScope::isAdmin($auth) ? null : $auth['user_id'];
        $where = $uid ? 'WHERE user_id = ?' : '';
        $params = $uid ? [$uid] : [];
        $types = $uid ? 'i' : '';

        $sql = "SELECT
            COUNT(*) as total,
            SUM(status='pendente') as pendente,
            SUM(status='aprovada') as aprovada,
            SUM(status='rejeitada') as rejeitada,
            SUM(status='expirada') as expirada
            FROM cotacoes $where";
        $stmt = $db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        Response::success(['stats' => $row]);
    }

    private function validate($data, $isUpdate = false)
    {
        $errors = [];
        if (empty($data['origin'])) $errors['origin'] = 'Origem é obrigatória';
        if (empty($data['destination'])) $errors['destination'] = 'Destino é obrigatório';
        return $errors;
    }
}
