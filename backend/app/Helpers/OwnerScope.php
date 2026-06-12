<?php

namespace App\Helpers;

use App\Config\Database;
use App\Helpers\Jwt;

class OwnerScope
{
    public static function userFromToken()
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

    public static function isAdmin($payload)
    {
        $appRole = $payload['user_metadata']['role'] ?? $payload['role'] ?? '';
        return $appRole === 'admin';
    }

    public static function canAccess($payload, $resourceUserId)
    {
        if (self::isAdmin($payload)) return true;
        return (int)$payload['user_id'] === (int)$resourceUserId;
    }

    public static function ensureOwnerOrAdmin($payload, $resourceUserId)
    {
        if (!self::canAccess($payload, $resourceUserId)) {
            Response::error('Acesso negado', 403);
        }
    }

    public static function generateReference($prefix, $table, $column)
    {
        $db = Database::connection();
        do {
            $code = $prefix . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $stmt = $db->prepare("SELECT id FROM {$table} WHERE {$column} = ? LIMIT 1");
            $stmt->bind_param('s', $code);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } while ($exists);
        return $code;
    }
}
