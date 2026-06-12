<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Jwt;
use App\Helpers\Response;

class UserController
{
    public const PERMISSIONS = [
        'dashboard.view',
        'clients.view', 'clients.manage',
        'embarques.view', 'embarques.manage',
        'cotacoes.view', 'cotacoes.manage',
        'documentos.view', 'documentos.manage',
        'contactos.view', 'contactos.manage',
        'chat.view', 'chat.reply',
        'visitors.view',
        'content.manage',
    ];

    public function index()
    {
        $this->requireAdmin();
        $db = Database::connection();
        $role = $_GET['role'] ?? null;
        if ($role && !in_array($role, ['admin', 'cliente', 'funcionario'], true)) {
            Response::error('Role inválido', 422);
        }
        $baseSelect = "id, username, name, email, phone, role, status, approval_status, position, permissions, photo, last_login, created_at, password_must_change, password_changed_at, locked_at, locked_reason";
        if ($role) {
            $stmt = $db->prepare("SELECT $baseSelect FROM users WHERE role = ? ORDER BY created_at DESC");
            $stmt->bind_param('s', $role);
            $stmt->execute();
            $result = $stmt->get_result();
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $row['permissions'] = $row['permissions'] ? json_decode($row['permissions'], true) : [];
                $row['password_must_change'] = (int)($row['password_must_change'] ?? 0) === 1;
                $users[] = $row;
            }
            $stmt->close();
        } else {
            $result = $db->query("SELECT $baseSelect FROM users ORDER BY created_at DESC");
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $row['permissions'] = $row['permissions'] ? json_decode($row['permissions'], true) : [];
                $row['password_must_change'] = (int)($row['password_must_change'] ?? 0) === 1;
                $users[] = $row;
            }
        }
        Response::success(['users' => $users]);
    }

    public function pending()
    {
        $this->requireAdmin();
        $db = Database::connection();
        $stmt = $db->prepare("SELECT id, username, name, email, phone, role, approval_status, created_at FROM users WHERE role = 'cliente' AND approval_status = 'pending' ORDER BY created_at ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        Response::success(['users' => $users]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT id, username, name, email, phone, role, status, approval_status, position, permissions, photo, created_at, last_login, password_must_change, password_changed_at, locked_at, locked_reason FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Utilizador não encontrado', 404);
        $row['permissions'] = $row['permissions'] ? json_decode($row['permissions'], true) : [];
        $row['password_must_change'] = (int)($row['password_must_change'] ?? 0) === 1;

        $company = null;
        if ($row['role'] === 'cliente') {
            $stmt = $db->prepare('SELECT * FROM companies WHERE user_id = ? LIMIT 1');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $company = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }

        Response::success(['user' => $row, 'company' => $company]);
    }

    public function store()
    {
        $this->requireAdmin();
        $data = Response::input();
        $username = trim($data['username'] ?? '');
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? 'cliente';
        $position = trim($data['position'] ?? '');
        $permissions = $data['permissions'] ?? [];

        if (!in_array($role, ['admin', 'cliente', 'funcionario'], true)) {
            Response::error('Role inválido', 422);
        }
        if ($username === '' || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            Response::error('Dados inválidos', 422);
        }

        $db = Database::connection();
        $check = $db->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
        $check->bind_param('ss', $email, $username);
        $check->execute();
        if ($check->get_result()->fetch_assoc()) {
            $check->close();
            Response::error('Já existe uma conta com este email ou nome de utilizador', 409);
        }
        $check->close();

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $approval = ($role === 'admin' || $role === 'funcionario') ? 'approved' : 'pending';
        $mustChange = ($role === 'funcionario') ? 1 : 0;

        $permsJson = null;
        if ($role === 'funcionario' && is_array($permissions)) {
            $valid = array_values(array_intersect($permissions, self::PERMISSIONS));
            $permsJson = json_encode($valid);
        }

        $stmt = $db->prepare('INSERT INTO users (username, name, email, phone, role, password, status, approval_status, position, permissions, password_must_change) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?)');
        $stmt->bind_param('sssssssssi', $username, $name, $email, $phone, $role, $hash, $approval, $position, $permsJson, $mustChange);
        if (!$stmt->execute()) {
            Response::error('Não foi possível criar: ' . $stmt->error, 500);
        }
        $newId = $stmt->insert_id;
        $stmt->close();

        if ($role === 'funcionario') {
            $admins = $db->query("SELECT id FROM users WHERE role = 'admin'");
            if ($admins) {
                $notif = $db->prepare("INSERT INTO notifications (user_id, type, title, body, link, icon) VALUES (?, 'new_employee', ?, ?, ?, 'bi-person-badge-fill')");
                $title = 'Novo funcionário criado: ' . $name;
                $body = "Cargo: " . ($position ?: 'Funcionário') . " · Email: {$email}";
                $link = '/admin/funcionarios';
                while ($a = $admins->fetch_assoc()) {
                    $notif->bind_param('isss', $a['id'], $title, $body, $link);
                    $notif->execute();
                }
                $notif->close();
            }
        }

        Response::success(['user_id' => $newId], 'Utilizador criado');
    }

    public function update($id)
    {
        $this->requireAdmin();
        $data = Response::input();
        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $role = $data['role'] ?? null;
        $status = isset($data['status']) ? (int)$data['status'] : null;
        $position = $data['position'] ?? null;
        $permissions = $data['permissions'] ?? null;
        $newPassword = $data['password'] ?? null;
        $approvalStatus = $data['approval_status'] ?? null;

        if ($role !== null && !in_array($role, ['admin', 'cliente', 'funcionario'], true)) {
            Response::error('Role inválido', 422);
        }

        if ($approvalStatus !== null && !in_array($approvalStatus, ['pending', 'approved', 'rejected'], true)) {
            Response::error('approval_status inválido', 422);
        }

        $db = Database::connection();
        $permsJson = null;
        if (is_array($permissions)) {
            $valid = array_values(array_intersect($permissions, self::PERMISSIONS));
            $permsJson = json_encode($valid);
        }

        $sets = ['name = ?', 'phone = ?'];
        $types = 'ss';
        $values = [$name, $phone];

        if ($role !== null) { $sets[] = 'role = ?'; $types .= 's'; $values[] = $role; }
        if ($status !== null) { $sets[] = 'status = ?'; $types .= 'i'; $values[] = $status; }
        if ($position !== null) { $sets[] = 'position = ?'; $types .= 's'; $values[] = $position; }
        if ($permsJson !== null) { $sets[] = 'permissions = ?'; $types .= 's'; $values[] = $permsJson; }
        if ($approvalStatus !== null) {
            $sets[] = 'approval_status = ?';
            $types .= 's';
            $values[] = $approvalStatus;
            if ($approvalStatus === 'approved') {
                $sets[] = 'approved_at = NOW()';
            }
        }
        if ($newPassword && strlen($newPassword) >= 6) {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $sets[] = 'password = ?'; $types .= 's'; $values[] = $hash;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?';
        $types .= 'i';
        $values[] = (int)$id;

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $stmt->close();

        Response::success([], 'Utilizador atualizado');
    }

    public function destroy($id)
    {
        $this->requireAdmin();
        $db = Database::connection();
        $check = $db->prepare('SELECT role FROM users WHERE id = ?');
        $check->bind_param('i', $id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();
        $check->close();
        if (!$row) Response::error('Utilizador não encontrado', 404);
        if ($row['role'] === 'admin') Response::error('Não é possível eliminar um administrador', 403);

        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        Response::success([], 'Utilizador removido');
    }

    public function approve($id)
    {
        $auth = $this->requireAdmin();
        $db = Database::connection();
        $stmt = $db->prepare("UPDATE users SET approval_status = 'approved', approved_at = NOW(), approved_by = ?, rejection_reason = NULL WHERE id = ?");
        $stmt->bind_param('ii', $auth['user_id'], $id);
        $stmt->execute();
        $stmt->close();
        Response::success([], 'Conta aprovada');
    }

    public function reject($id)
    {
        $auth = $this->requireAdmin();
        $data = Response::input();
        $reason = trim($data['reason'] ?? 'Conta rejeitada pelo administrador');
        $db = Database::connection();
        $stmt = $db->prepare("UPDATE users SET approval_status = 'rejected', approved_by = ?, rejection_reason = ? WHERE id = ?");
        $stmt->bind_param('isi', $auth['user_id'], $reason, $id);
        $stmt->execute();
        $stmt->close();
        Response::success([], 'Conta rejeitada');
    }

    public function pendingCount()
    {
        $this->requireAdmin();
        $db = Database::connection();
        $result = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'cliente' AND approval_status = 'pending'");
        $row = $result->fetch_assoc();
        Response::success(['count' => (int)$row['total']]);
    }

    public function lock($id)
    {
        $auth = $this->requireAdmin();
        $data = Response::input();
        $reason = trim($data['reason'] ?? 'Conta bloqueada pelo administrador');
        $duration = max(1, min(168, (int)($data['duration_hours'] ?? 24)));

        $db = Database::connection();
        $stmt = $db->prepare('SELECT role, status FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Utilizador não encontrado', 404);
        if ($row['role'] === 'admin') Response::error('Não é possível bloquear um administrador', 403);

        $stmt = $db->prepare('UPDATE users SET status = 0, locked_at = NOW(), locked_reason = ? WHERE id = ?');
        $stmt->bind_param('si', $reason, $id);
        $stmt->execute();
        $stmt->close();

        Response::success(['duration_hours' => $duration, 'reason' => $reason], 'Conta bloqueada por ' . $duration . 'h');
    }

    public function unlock($id)
    {
        $auth = $this->requireAdmin();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Utilizador não encontrado', 404);

        $stmt = $db->prepare('UPDATE users SET status = 1, locked_at = NULL, locked_reason = NULL, password_must_change = 0, password_changed_at = NOW() WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        Response::success([], 'Conta desbloqueada e reativada');
    }

    public function permissions()
    {
        Response::success(['permissions' => self::PERMISSIONS]);
    }

    private function requireAdmin()
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.+)/i', $header, $m)) {
            Response::error('Token em falta', 401);
        }
        $payload = Jwt::decode($m[1]);
        if (!$payload) {
            Response::error('Token inválido', 401);
        }
        $appRole = $payload['user_metadata']['role'] ?? $payload['role'] ?? '';
        if ($appRole !== 'admin') {
            Response::error('Acesso restrito a administradores', 403);
        }
        return $payload;
    }
}
