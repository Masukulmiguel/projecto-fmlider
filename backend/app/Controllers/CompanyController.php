<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Jwt;
use App\Helpers\Response;

class CompanyController
{
    public function show()
    {
        $auth = $this->userFromToken();
        $db = Database::connection();

        $stmt = $db->prepare('SELECT * FROM companies WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $auth['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            Response::success(['company' => null, 'is_completed' => false]);
            return;
        }

        Response::success(['company' => $row, 'is_completed' => (bool)((int)$row['is_completed'])]);
    }

    public function store()
    {
        $auth = $this->userFromToken();
        $this->ensureApprovedCliente($auth);

        $data = $this->collectData();
        $errors = $this->validate($data, false);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $db = Database::connection();

        $stmt = $db->prepare('SELECT id FROM companies WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $auth['user_id']);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $stmt = $db->prepare('UPDATE companies SET company_name = ?, nif = ?, logo = CASE WHEN ? = "" THEN logo ELSE ? END, address = ?, phone = ?, email = ?, service = ?, case_description = ?, is_completed = 1 WHERE user_id = ?');
            $logo = !empty($data['logo']) ? $data['logo'] : '';
            $stmt->bind_param('ssssssssi',
                $data['company_name'],
                $data['nif'],
                $logo,
                $logo,
                $data['address'],
                $data['phone'],
                $data['email'],
                $data['service'],
                $data['case_description'],
                $auth['user_id']
            );
            $stmt->execute();
            $stmt->close();
            Response::success([], 'Dados da empresa atualizados');
            return;
        }

        $logo = !empty($data['logo']) ? $data['logo'] : '';
        $isPublished = !empty($logo) ? 1 : 0;

        $stmt = $db->prepare('INSERT INTO companies (user_id, company_name, nif, logo, address, phone, email, service, case_description, is_completed, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)');
        $stmt->bind_param('issssssssi',
            $auth['user_id'],
            $data['company_name'],
            $data['nif'],
            $logo,
            $data['address'],
            $data['phone'],
            $data['email'],
            $data['service'],
            $data['case_description'],
            $isPublished
        );

        if (!$stmt->execute()) {
            Response::error('Erro a guardar: ' . $stmt->error, 500);
        }
        $newId = $stmt->insert_id;
        $stmt->close();

        Response::success(['company_id' => $newId], 'Empresa configurada. Bem-vindo ao seu dashboard.');
    }

    public function update()
    {
        $auth = $this->userFromToken();
        $this->ensureApprovedCliente($auth);

        $data = $this->collectData();
        $errors = $this->validate($data, true);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $db = Database::connection();

        $stmt = $db->prepare('SELECT id, logo, is_published FROM companies WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $auth['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $logo = !empty($data['logo']) ? $data['logo'] : ($row['logo'] ?? '');
        $isPublished = $row['is_published'] ?? 0;

        if (!$row) {
            $stmt = $db->prepare('INSERT INTO companies (user_id, company_name, nif, logo, address, phone, email, service, case_description, is_completed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)');
            $stmt->bind_param('issssssss',
                $auth['user_id'],
                $data['company_name'],
                $data['nif'],
                $logo,
                $data['address'],
                $data['phone'],
                $data['email'],
                $data['service'],
                $data['case_description']
            );
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $db->prepare('UPDATE companies SET company_name = ?, nif = ?, logo = ?, address = ?, phone = ?, email = ?, service = ?, case_description = ?, is_completed = 1 WHERE user_id = ?');
            $stmt->bind_param('ssssssssi',
                $data['company_name'],
                $data['nif'],
                $logo,
                $data['address'],
                $data['phone'],
                $data['email'],
                $data['service'],
                $data['case_description'],
                $auth['user_id']
            );
            $stmt->execute();
            $stmt->close();
        }

        Response::success([], 'Dados da empresa atualizados');
    }

    public function uploadLogo()
    {
        $auth = $this->userFromToken();
        $this->ensureApprovedCliente($auth);

        if (empty($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            Response::error('Nenhum ficheiro enviado', 422);
        }

        $file = $_FILES['logo'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
        if (!in_array($file['type'], $allowed, true)) {
            Response::error('Formato inválido. Use JPG, PNG, WEBP, GIF ou SVG.', 422);
        }
        if ($file['size'] > 3 * 1024 * 1024) {
            Response::error('Ficheiro demasiado grande (máx 3MB)', 422);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $ext = strtolower($ext ?: 'png');
        $name = 'logo_' . $auth['user_id'] . '_' . time() . '.' . $ext;
        $dir = BASE_PATH . '/storage/uploads/logos';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $dest = $dir . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            Response::error('Falha ao guardar o ficheiro', 500);
        }

        $publicPath = '/backend/storage/uploads/logos/' . $name;

        $db = Database::connection();

        $stmt = $db->prepare('SELECT id FROM companies WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $auth['user_id']);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $stmt = $db->prepare('UPDATE companies SET logo = ?, is_published = 1 WHERE user_id = ?');
            $stmt->bind_param('si', $publicPath, $auth['user_id']);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $db->prepare('INSERT INTO companies (user_id, company_name, logo, is_completed, is_published) VALUES (?, ?, ?, 0, 1)');
            $stmt->bind_param('iss', $auth['user_id'], $auth['email'], $publicPath);
            $stmt->execute();
            $stmt->close();
        }

        Response::success(['logo' => $publicPath, 'is_published' => true], 'Logotipo atualizado e publicado no site');
    }

    public function publicCarousel()
    {
        $db = Database::connection();
        $sql = "SELECT DISTINCT c.id, c.company_name, c.logo, c.service
                FROM companies c
                INNER JOIN users u ON u.id = c.user_id
                WHERE c.logo IS NOT NULL
                  AND c.logo <> ''
                  AND c.is_published = 1
                  AND u.approval_status = 'approved'
                GROUP BY c.user_id
                ORDER BY c.updated_at DESC";
        $r = $db->query($sql);
        $items = [];
        if ($r) {
            while ($row = $r->fetch_assoc()) {
                $items[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['company_name'],
                    'logo' => $row['logo'],
                    'service' => $row['service'],
                ];
            }
        }
        Response::success(['items' => $items, 'count' => count($items)]);
    }

    public function togglePublish()
    {
        $auth = $this->userFromToken();
        $this->ensureApprovedCliente($auth);

        $data = Response::input();
        $flag = !empty($data['is_published']);

        $db = Database::connection();
        $stmt = $db->prepare('SELECT logo FROM companies WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $auth['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Configure primeiro os dados da empresa', 404);
        if ($flag && (empty($row['logo']) || $row['logo'] === '')) {
            Response::error('Carregue o logotipo antes de publicar', 422);
        }

        $stmt = $db->prepare('UPDATE companies SET is_published = ? WHERE user_id = ?');
        $val = $flag ? 1 : 0;
        $uid = (int)$auth['user_id'];
        $stmt->bind_param('ii', $val, $uid);
        $stmt->execute();
        $stmt->close();

        Response::success(['is_published' => $flag], $flag ? 'Logo publicado no site' : 'Logo removido do site');
    }

    private function collectData()
    {
        $data = Response::input();
        return [
            'company_name' => trim($data['company_name'] ?? ''),
            'nif' => trim($data['nif'] ?? ''),
            'logo' => trim($data['logo'] ?? ''),
            'address' => trim($data['address'] ?? ''),
            'phone' => trim($data['phone'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'service' => trim($data['service'] ?? ''),
            'case_description' => trim($data['case_description'] ?? ''),
        ];
    }

    private function validate($data, $isUpdate)
    {
        $errors = [];
        if ($data['company_name'] === '') $errors['company_name'] = 'Nome da empresa é obrigatório';
        if ($data['address'] === '') $errors['address'] = 'Endereço é obrigatório';
        if ($data['phone'] === '') $errors['phone'] = 'Telefone é obrigatório';
        if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email inválido';
        }
        if ($data['service'] === '') $errors['service'] = 'Serviço é obrigatório';
        if ($data['case_description'] === '') $errors['case_description'] = 'Descrição do caso é obrigatória';
        return $errors;
    }

    private function ensureApprovedCliente($auth)
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT approval_status, role FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $auth['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Utilizador não encontrado', 404);
        if ($row['role'] !== 'cliente') Response::error('Apenas clientes podem configurar empresa', 403);
        if ($row['approval_status'] !== 'approved') Response::error('Conta ainda não aprovada', 403);
    }

    private function userFromToken()
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.+)/i', $header, $m)) {
            Response::error('Token em falta', 401);
        }
        $payload = Jwt::decode($m[1]);
        if (!$payload) {
            Response::error('Token inválido ou expirado', 401);
        }
        return $payload;
    }
}
