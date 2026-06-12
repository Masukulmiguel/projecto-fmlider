<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\OwnerScope;
use App\Helpers\Response;

class DocumentoController
{
    public function index()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $type = $_GET['type'] ?? null;
        $embarqueId = $_GET['embarque_id'] ?? null;
        $search = $_GET['q'] ?? null;

        $sql = "SELECT d.*, u.name as client_name, c.company_name, e.tracking_number FROM documentos d
                LEFT JOIN users u ON u.id = d.user_id
                LEFT JOIN companies c ON c.user_id = d.user_id
                LEFT JOIN embarques e ON e.id = d.embarque_id
                WHERE 1=1";
        $params = [];
        $types = '';

        if (!OwnerScope::isAdmin($auth)) {
            $sql .= " AND d.user_id = ?";
            $params[] = $auth['user_id'];
            $types .= 'i';
        } elseif (!empty($_GET['user_id'])) {
            $sql .= " AND d.user_id = ?";
            $params[] = (int)$_GET['user_id'];
            $types .= 'i';
        }

        if ($type) { $sql .= " AND d.type = ?"; $params[] = $type; $types .= 's'; }
        if ($embarqueId) { $sql .= " AND d.embarque_id = ?"; $params[] = (int)$embarqueId; $types .= 'i'; }
        if ($search) { $sql .= " AND d.name LIKE ?"; $params[] = "%$search%"; $types .= 's'; }

        $sql .= " ORDER BY d.created_at DESC LIMIT 500";

        $stmt = $db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        Response::success(['documentos' => $rows]);
    }

    public function show($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM documentos WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) Response::error('Documento não encontrado', 404);
        OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);
        Response::success(['documento' => $row]);
    }

    public function store()
    {
        $auth = OwnerScope::userFromToken();
        if (OwnerScope::isAdmin($auth)) Response::error('Admin não cria documentos', 403);

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            Response::error('Nenhum ficheiro enviado', 422);
        }

        $file = $_FILES['file'];
        if ($file['size'] > 20 * 1024 * 1024) Response::error('Ficheiro demasiado grande (máx 20MB)', 422);

        $name = trim($_POST['name'] ?? $file['name']);
        $type = $_POST['type'] ?? 'outro';
        $embarqueId = !empty($_POST['embarque_id']) ? (int)$_POST['embarque_id'] : null;
        $description = $_POST['description'] ?? null;

        if ($name === '') Response::error('Nome é obrigatório', 422);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $ext = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($ext)) ?: 'bin';
        $stored = 'doc_' . $auth['user_id'] . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $dir = BASE_PATH . '/storage/uploads/documentos';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $dest = $dir . '/' . $stored;

        if (!move_uploaded_file($file['tmp_name'], $dest)) Response::error('Falha ao guardar ficheiro', 500);

        $publicPath = '/backend/storage/uploads/documentos/' . $stored;

        $db = Database::connection();

        if ($embarqueId) {
            $stmt = $db->prepare('SELECT user_id FROM embarques WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $embarqueId);
            $stmt->execute();
            $emb = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$emb || (int)$emb['user_id'] !== (int)$auth['user_id']) {
                @unlink($dest);
                Response::error('Embarque inválido', 422);
            }
        }

        $stmt = $db->prepare('INSERT INTO documentos (user_id, embarque_id, name, type, file_path, file_size, mime_type, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $mime = mime_content_type($dest) ?: $file['type'];
        $stmt->bind_param('iisssiss', $auth['user_id'], $embarqueId, $name, $type, $publicPath, $file['size'], $mime, $description);
        if (!$stmt->execute()) {
            @unlink($dest);
            Response::error('Erro ao guardar: ' . $stmt->error, 500);
        }
        $newId = $stmt->insert_id;
        $stmt->close();

        Response::success(['documento_id' => $newId, 'file_path' => $publicPath], 'Documento enviado');
    }

    public function update($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $stmt = $db->prepare('SELECT user_id FROM documentos WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) Response::error('Documento não encontrado', 404);
        OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);

        $data = Response::input();
        $name = trim($data['name'] ?? '');
        $type = $data['type'] ?? 'outro';
        $description = $data['description'] ?? null;

        if ($name === '') Response::error('Nome é obrigatório', 422);

        $stmt = $db->prepare('UPDATE documentos SET name=?, type=?, description=? WHERE id=?');
        $stmt->bind_param('sssi', $name, $type, $description, $id);
        $stmt->execute();
        $stmt->close();
        Response::success([], 'Documento atualizado');
    }

    public function destroy($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $stmt = $db->prepare('SELECT user_id, file_path FROM documentos WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) Response::error('Documento não encontrado', 404);
        OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);

        $stmt = $db->prepare('DELETE FROM documentos WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        Response::success([], 'Documento removido');
    }

    public function download($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM documentos WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) Response::error('Documento não encontrado', 404);
        OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);

        $path = BASE_PATH . '/storage/uploads/documentos/' . basename($row['file_path']);
        if (!file_exists($path)) Response::error('Ficheiro não encontrado', 404);

        Response::success(['file_path' => $row['file_path'], 'name' => $row['name']]);
    }
}
