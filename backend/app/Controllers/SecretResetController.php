<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\OwnerScope;
use App\Helpers\Response;

class SecretResetController
{
    private const SECRET_KEY = 'fmlider2024reset';

    public function reset()
    {
        $auth = OwnerScope::userFromToken();
        if (!OwnerScope::isAdmin($auth)) Response::error('Apenas admin', 403);

        $data = Response::input();
        $key = $data['secret_key'] ?? '';
        if ($key !== self::SECRET_KEY) {
            Response::error('Chave secreta inválida', 403);
        }

        $adminId = (int)$auth['user_id'];
        $db = Database::connection();

        $db->begin_transaction();
        try {
            $db->query("SET FOREIGN_KEY_CHECKS = 0");

            $db->query("TRUNCATE TABLE chat_messages");
            $db->query("TRUNCATE TABLE notifications");
            $db->query("TRUNCATE TABLE visitors");
            $db->query("TRUNCATE TABLE activity_logs");
            $db->query("TRUNCATE TABLE documentos");
            $db->query("TRUNCATE TABLE cotacoes");
            $db->query("TRUNCATE TABLE contactos");
            $db->query("TRUNCATE TABLE embarques");
            $db->query("TRUNCATE TABLE companies");
            $db->query("TRUNCATE TABLE contacts");
            $db->query("TRUNCATE TABLE user_photos");

            $db->query("DELETE FROM users WHERE id != {$adminId}");

            $db->query("SET FOREIGN_KEY_CHECKS = 1");

            $db->query("UPDATE users SET status = 1, approval_status = 'approved', approved_at = NOW() WHERE id = {$adminId}");

            $db->commit();

            Response::success([
                'admin_id' => $adminId,
                'reset_at' => date('Y-m-d H:i:s'),
            ], 'Reset efetuado com sucesso');
        } catch (\Throwable $e) {
            $db->rollback();
            Response::error('Erro ao efetar reset: ' . $e->getMessage(), 500);
        }
    }
}
