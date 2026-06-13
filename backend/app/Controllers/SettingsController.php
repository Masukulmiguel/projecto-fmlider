<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;

class SettingsController
{
    public function index()
    {
        $db = Database::connection();
        $result = $db->query("SELECT `key`, `value` FROM settings");
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['key']] = $row['value'];
        }
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'settings' => $settings], JSON_UNESCAPED_UNICODE);
    }

    public function get($key)
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT `value` FROM settings WHERE `key` = ?");
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        if ($row) {
            return json_encode(['success' => true, 'key' => $key, 'value' => $row['value']], JSON_UNESCAPED_UNICODE);
        }
        return json_encode(['success' => true, 'key' => $key, 'value' => null], JSON_UNESCAPED_UNICODE);
    }

    public function update()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data) || !is_array($data)) {
            Response::error('Dados inválidos', 422);
        }

        $db = Database::connection();
        $stmt = $db->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        foreach ($data as $key => $value) {
            $k = $key;
            $v = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
            $stmt->bind_param('ss', $k, $v);
            $stmt->execute();
        }
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Configurações atualizadas'], JSON_UNESCAPED_UNICODE);
    }

    public function destroy($key)
    {
        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM settings WHERE `key` = ?");
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(['success' => true, 'message' => 'Configuração eliminada'], JSON_UNESCAPED_UNICODE);
    }
}
