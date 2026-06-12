<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Jwt;
use App\Helpers\Response;

class AuthController
{
    public function login()
    {
        $data = Response::input();
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if ($email === '' || $password === '') {
            Response::error('Email e senha são obrigatórios', 422);
        }

        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($password, $user['password'])) {
            Response::error('Credenciais inválidas', 401);
        }

        if (!empty($user['locked_at'])) {
            $lockedTs = strtotime($user['locked_at']);
            $expiresAt = $lockedTs + (24 * 3600);
            if (time() < $expiresAt) {
                $remaining = $expiresAt - time();
                $hours = floor($remaining / 3600);
                $minutes = floor(($remaining % 3600) / 60);
                $reason = $user['locked_reason'] ? ' Motivo: ' . $user['locked_reason'] : '';
                Response::error(sprintf(
                    'Conta bloqueada temporariamente. Tente novamente em %dh %dmin.%s',
                    $hours, $minutes, $reason
                ), 423, ['retry_after' => $remaining]);
            }
        }

        if ((int)$user['status'] === 0 && empty($user['locked_at'])) {
            Response::error('Conta desativada. Contacte o administrador.', 403);
        }

        if ($user['role'] !== 'admin' && $user['approval_status'] === 'pending') {
            Response::error('A sua conta ainda não foi aprovada pelo administrador.', 403);
        }

        if ($user['role'] !== 'admin' && $user['approval_status'] === 'rejected') {
            $reason = $user['rejection_reason'] ? ': ' . $user['rejection_reason'] : '.';
            Response::error('A sua conta foi rejeitada' . $reason, 403);
        }

        $companyCompleted = false;
        if ($user['role'] === 'cliente') {
            $stmt = $db->prepare('SELECT is_completed FROM companies WHERE user_id = ? LIMIT 1');
            $stmt->bind_param('i', $user['id']);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $companyCompleted = $row ? (bool)((int)$row['is_completed']) : false;
        } else {
            $companyCompleted = true;
        }

        $stmt = $db->prepare('UPDATE users SET last_login = NOW(), locked_at = NULL, locked_reason = NULL WHERE id = ?');
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $stmt->close();

        $permissions = [];
        if (!empty($user['permissions'])) {
            $decoded = json_decode($user['permissions'], true);
            if (is_array($decoded)) $permissions = $decoded;
        }

        $token = Jwt::encode([
            'user_id' => (int)$user['id'],
            'role' => $user['role'],
            'email' => $user['email'],
            'permissions' => $permissions,
        ]);

        $mustChange = (int)($user['password_must_change'] ?? 0) === 1;

        unset($user['password']);

        Response::success([
            'token' => $token,
            'user' => $this->formatUser($user, $companyCompleted),
            'must_change_password' => $mustChange,
        ], $mustChange ? 'É necessário alterar a senha no primeiro acesso' : 'Login efetuado com sucesso');
    }

    public function register()
    {
        $data = Response::input();
        $username = trim($data['username'] ?? '');
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';
        $passwordConfirm = $data['password_confirm'] ?? $data['password_confirmation'] ?? '';

        $errors = [];
        if (strlen($username) < 3) $errors['username'] = 'Nome de utilizador deve ter pelo menos 3 caracteres';
        if ($name === '') $errors['name'] = 'Nome é obrigatório';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email inválido';
        if (strlen($password) < 6) $errors['password'] = 'Senha deve ter pelo menos 6 caracteres';
        if ($password !== $passwordConfirm) $errors['password_confirm'] = 'As senhas não coincidem';

        if (!empty($errors)) {
            Response::error('Verifique os campos do formulário', 422, $errors);
        }

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->bind_param('ss', $email, $username);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            Response::error('Já existe uma conta com este email ou nome de utilizador', 409);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $role = 'cliente';
        $approval = 'pending';
        $status = 1;

        $stmt = $db->prepare('INSERT INTO users (username, name, email, phone, role, password, status, approval_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssssss', $username, $name, $email, $phone, $role, $hash, $status, $approval);

        if (!$stmt->execute()) {
            Response::error('Não foi possível criar a conta: ' . $stmt->error, 500);
        }
        $newId = $stmt->insert_id;
        $stmt->close();

        $admins = $db->query("SELECT id FROM users WHERE role = 'admin'");
        if ($admins) {
            $notif = $db->prepare("INSERT INTO notifications (user_id, type, title, body, link, icon) VALUES (?, 'new_client', ?, ?, ?, 'bi-person-plus-fill')");
            $title = 'Novo cliente registado: ' . $name;
            $body = "Email: {$email}" . (!empty($phone) ? " · Telefone: {$phone}" : '');
            $link = '/admin/utilizadores';
            while ($a = $admins->fetch_assoc()) {
                $notif->bind_param('isss', $a['id'], $title, $body, $link);
                $notif->execute();
            }
            $notif->close();
        }

        Response::success([
            'user_id' => $newId,
            'username' => $username,
            'email' => $email,
        ], 'Conta criada. Aguarde aprovação do administrador para aceder ao dashboard.');
    }

    public function logout()
    {
        Response::success([], 'Sessão terminada');
    }

    public function getProfile()
    {
        $auth = $this->userFromToken();
        $db = Database::connection();

        $email = $auth['email'] ?? '';
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            Response::success([
                'user' => [
                    'id' => $auth['user_id'] ?? 0,
                    'email' => $email,
                    'name' => $auth['name'] ?? $auth['email'] ?? $email,
                    'phone' => $auth['phone'] ?? '',
                    'role' => $auth['role'] ?? 'cliente',
                    'position' => null,
                    'permissions' => $auth['permissions'] ?? [],
                    'approval_status' => $auth['approval_status'] ?? 'approved',
                    'company_completed' => $auth['company_completed'] ?? true,
                    'photo' => $auth['photo'] ?? null,
                    'must_change_password' => false,
                    'password_changed_at' => null,
                    'locked_at' => null,
                    'locked_reason' => null,
                    'created_at' => null,
                ],
                'company' => null,
                'photo_history' => [],
            ]);
            return;
        }

        $companyCompleted = true;
        $company = null;
        if ($row['role'] === 'cliente') {
            $stmt = $db->prepare('SELECT * FROM companies WHERE user_id = ? LIMIT 1');
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();
            $company = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $companyCompleted = $company ? (bool)((int)$company['is_completed']) : false;
        }

        unset($row['password']);
        $photoHistory = $this->loadPhotoHistory($row['id']);
        Response::success([
            'user' => $this->formatUser($row, $companyCompleted),
            'company' => $company,
            'photo_history' => $photoHistory,
        ]);
    }

    public function updateProfile()
    {
        $auth = $this->userFromToken();
        $data = Response::input();

        $db = Database::connection();
        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if ($name === '') Response::error('Nome é obrigatório', 422);

        $stmt = $db->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
        $stmt->bind_param('ssi', $name, $phone, $auth['user_id']);
        $stmt->execute();
        $stmt->close();

        Response::success([], 'Perfil atualizado');
    }

    public function resetPassword()
    {
        $data = Response::input();
        $email = trim($data['email'] ?? '');
        if ($email === '') Response::error('Email é obrigatório', 422);

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        Response::success([], 'Se o email existir, receberá instruções para redefinir a senha.');
    }

    public function changePassword()
    {
        $auth = $this->userFromToken();
        $data = Response::input();
        $current = $data['current_password'] ?? '';
        $new = $data['new_password'] ?? '';
        $confirm = $data['new_password_confirmation'] ?? $data['password_confirmation'] ?? '';

        if (strlen($new) < 6) Response::error('Nova senha deve ter pelo menos 6 caracteres', 422);
        if ($new !== $confirm) Response::error('A confirmação da nova senha não coincide', 422);

        $db = Database::connection();
        $stmt = $db->prepare('SELECT password, password_must_change FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $auth['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Utilizador não encontrado', 404);

        $isForced = (int)($row['password_must_change'] ?? 0) === 1;
        if (!$isForced) {
            if ($current === '' || !password_verify($current, $row['password'])) {
                Response::error('Senha atual incorreta', 401);
            }
        }

        $hash = password_hash($new, PASSWORD_BCRYPT);
        $stmt = $db->prepare('UPDATE users SET password = ?, password_must_change = 0, password_changed_at = NOW(), locked_at = NULL, locked_reason = NULL WHERE id = ?');
        $stmt->bind_param('si', $hash, $auth['user_id']);
        $stmt->execute();
        $stmt->close();

        Response::success(['password_changed' => true], 'Senha alterada com sucesso');
    }

    public function uploadPhoto()
    {
        $auth = $this->userFromToken();

        if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            Response::error('Nenhum ficheiro enviado', 422);
        }

        $file = $_FILES['photo'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if (!isset($allowed[$file['type']])) {
            Response::error('Formato inválido. Use JPG, PNG, WEBP ou GIF.', 422);
        }
        if ($file['size'] > 3 * 1024 * 1024) {
            Response::error('Ficheiro demasiado grande (máx 3MB)', 422);
        }

        $ext = $allowed[$file['type']];
        $name = 'photo_' . $auth['user_id'] . '_' . time() . '.' . $ext;
        $dir = BASE_PATH . '/storage/uploads/photos';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $dest = $dir . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            Response::error('Falha ao guardar o ficheiro', 500);
        }

        $publicPath = '/backend/storage/uploads/photos/' . $name;

        $db = Database::connection();
        $stmt = $db->prepare('UPDATE users SET photo = ? WHERE id = ?');
        $stmt->bind_param('si', $publicPath, $auth['user_id']);
        $stmt->execute();
        $stmt->close();

        try {
            $stmt = $db->prepare('UPDATE user_photos SET is_current = 0 WHERE user_id = ?');
            $stmt->bind_param('i', $auth['user_id']);
            $stmt->execute();
            $stmt->close();

            $stmt = $db->prepare('INSERT INTO user_photos (user_id, photo_path, is_current) VALUES (?, ?, 1)');
            $stmt->bind_param('is', $auth['user_id'], $publicPath);
            $stmt->execute();
            $stmt->close();
        } catch (\Exception $e) {}

        $history = $this->loadPhotoHistory($auth['user_id']);

        Response::success([
            'photo' => $publicPath,
            'history' => $history,
        ], 'Foto atualizada');
    }

    public function restorePhoto()
    {
        $auth = $this->userFromToken();
        $data = Response::input();
        $photoId = (int)($data['photo_id'] ?? 0);
        if (!$photoId) Response::error('photo_id obrigatório', 422);

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id, photo_path FROM user_photos WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->bind_param('ii', $photoId, $auth['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) Response::error('Foto não encontrada', 404);

        $stmt = $db->prepare('UPDATE user_photos SET is_current = 0 WHERE user_id = ?');
        $stmt->bind_param('i', $auth['user_id']);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare('UPDATE user_photos SET is_current = 1 WHERE id = ?');
        $stmt->bind_param('i', $photoId);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare('UPDATE users SET photo = ? WHERE id = ?');
        $stmt->bind_param('si', $row['photo_path'], $auth['user_id']);
        $stmt->execute();
        $stmt->close();

        Response::success([
            'photo' => $row['photo_path'],
            'history' => $this->loadPhotoHistory($auth['user_id']),
        ], 'Foto reposta');
    }

    private function loadPhotoHistory($userId)
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT id, photo_path, is_current, created_at FROM user_photos WHERE user_id = ? ORDER BY created_at DESC LIMIT 24');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return array_map(function ($r) {
                return [
                    'id' => (int)$r['id'],
                    'photo' => $r['photo_path'],
                    'is_current' => (int)$r['is_current'] === 1,
                    'created_at' => $r['created_at'],
                ];
            }, $rows);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function userFromToken()
    {
        if (isset($_REQUEST['_supabase_user'])) {
            return $_REQUEST['_supabase_user'];
        }

        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.+)/i', $header, $m)) {
            Response::error('Token em falta', 401);
        }

        $payload = Jwt::decode($m[1]);
        if (!$payload) {
            Response::error('Token inválido ou expirado', 401);
        }

        if (!isset($payload['user_id']) && isset($payload['sub'])) {
            $email = $payload['email'] ?? '';
            $db = Database::connection();
            $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $payload['user_id'] = $row ? (int)$row['id'] : 0;
        }

        return $payload;
    }

    private function formatUser($user, $companyCompleted = true)
    {
        $permissions = [];
        if (!empty($user['permissions'])) {
            $decoded = json_decode($user['permissions'], true);
            if (is_array($decoded)) $permissions = $decoded;
        }
        return [
            'id' => (int)$user['id'],
            'username' => $user['username'] ?? null,
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => $user['role'],
            'position' => $user['position'] ?? null,
            'permissions' => $permissions,
            'approval_status' => $user['approval_status'] ?? 'approved',
            'company_completed' => $companyCompleted,
            'photo' => $user['photo'] ?? null,
            'must_change_password' => (int)($user['password_must_change'] ?? 0) === 1,
            'password_changed_at' => $user['password_changed_at'] ?? null,
            'locked_at' => $user['locked_at'] ?? null,
            'locked_reason' => $user['locked_reason'] ?? null,
            'created_at' => $user['created_at'] ?? null,
        ];
    }
}
