<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\OwnerScope;
use App\Helpers\Response;

class EmbarqueController
{
    public function index()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $status = $_GET['status'] ?? null;
        $type = $_GET['type'] ?? null;
        $search = $_GET['q'] ?? null;

        $sql = "SELECT e.*, u.name as client_name, c.company_name FROM embarques e
                LEFT JOIN users u ON u.id = e.user_id
                LEFT JOIN companies c ON c.user_id = e.user_id
                WHERE 1=1";
        $params = [];
        $types = '';

        if (!OwnerScope::isAdmin($auth)) {
            $sql .= " AND e.user_id = ?";
            $params[] = $auth['user_id'];
            $types .= 'i';
        } elseif (!empty($_GET['user_id'])) {
            $sql .= " AND e.user_id = ?";
            $params[] = (int)$_GET['user_id'];
            $types .= 'i';
        }

        if ($status) { $sql .= " AND e.status = ?"; $params[] = $status; $types .= 's'; }
        if ($type) { $sql .= " AND e.type = ?"; $params[] = $type; $types .= 's'; }
        if ($search) { $sql .= " AND (e.tracking_number LIKE ? OR e.origin LIKE ? OR e.destination LIKE ?)"; $like = "%$search%"; $params = array_merge($params, [$like, $like, $like]); $types .= 'sss'; }

        $sql .= " ORDER BY e.created_at DESC LIMIT 500";

        $stmt = $db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        Response::success(['embarques' => $rows]);
    }

    public function show($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $stmt = $db->prepare("SELECT e.*, u.name as client_name, c.company_name FROM embarques e LEFT JOIN users u ON u.id = e.user_id LEFT JOIN companies c ON c.user_id = e.user_id WHERE e.id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Embarque não encontrado', 404);
        OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);

        Response::success(['embarque' => $row]);
    }

    public function store()
    {
        $auth = OwnerScope::userFromToken();
        if (OwnerScope::isAdmin($auth)) Response::error('Admin não cria embarques para clientes', 403);

        $data = Response::input();
        $errors = $this->validate($data);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $tracking = OwnerScope::generateReference('EMB', 'embarques', 'tracking_number');

        $db = Database::connection();
        $stmt = $db->prepare("INSERT INTO embarques (user_id, tracking_number, origin, destination, type, status, weight, volume, declared_value, currency, ship_date, delivery_date, description, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $type = $data['type'] ?? 'maritimo';
        $status = $data['status'] ?? 'pendente';
        $weight = (float)($data['weight'] ?? 0);
        $volume = (float)($data['volume'] ?? 0);
        $declared = (float)($data['declared_value'] ?? 0);
        $currency = $data['currency'] ?? 'AOA';
        $ship = !empty($data['ship_date']) ? $data['ship_date'] : null;
        $delivery = !empty($data['delivery_date']) ? $data['delivery_date'] : null;

        $stmt->bind_param('isssssdddsssss',
            $auth['user_id'],
            $tracking,
            $data['origin'],
            $data['destination'],
            $type,
            $status,
            $weight,
            $volume,
            $declared,
            $currency,
            $ship,
            $delivery,
            $data['description'] ?? null,
            $data['notes'] ?? null
        );

        if (!$stmt->execute()) Response::error('Erro ao criar: ' . $stmt->error, 500);
        $newId = $stmt->insert_id;
        $stmt->close();

        Response::success(['embarque_id' => $newId, 'tracking_number' => $tracking], 'Embarque criado');
    }

    public function update($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $stmt = $db->prepare('SELECT user_id FROM embarques WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Embarque não encontrado', 404);
        OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);

        $data = Response::input();
        $errors = $this->validate($data, true);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $ship = !empty($data['ship_date']) ? $data['ship_date'] : null;
        $delivery = !empty($data['delivery_date']) ? $data['delivery_date'] : null;

        $stmt = $db->prepare("UPDATE embarques SET origin=?, destination=?, type=?, status=?, weight=?, volume=?, declared_value=?, currency=?, ship_date=?, delivery_date=?, description=?, notes=? WHERE id=?");
        $stmt->bind_param('ssssdddsssssi',
            $data['origin'],
            $data['destination'],
            $data['type'] ?? 'maritimo',
            $data['status'] ?? 'pendente',
            (float)($data['weight'] ?? 0),
            (float)($data['volume'] ?? 0),
            (float)($data['declared_value'] ?? 0),
            $data['currency'] ?? 'AOA',
            $ship,
            $delivery,
            $data['description'] ?? null,
            $data['notes'] ?? null,
            $id
        );
        $stmt->execute();
        $stmt->close();

        Response::success([], 'Embarque atualizado');
    }

    public function destroy($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $stmt = $db->prepare('SELECT user_id FROM embarques WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Embarque não encontrado', 404);
        OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);

        $stmt = $db->prepare('DELETE FROM embarques WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        Response::success([], 'Embarque removido');
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
            SUM(status='em_transito') as em_transito,
            SUM(status='entregue') as entregue,
            SUM(status='cancelado') as cancelado
            FROM embarques $where";
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
