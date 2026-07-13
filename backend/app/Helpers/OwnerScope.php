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

        // Check token blacklist (logout)
        if (isset($payload['user_id'])) {
            try {
                $db = Database::connection();
                $stmt = $db->prepare('SELECT token_blacklisted_at FROM users WHERE id = ? LIMIT 1');
                $stmt->bind_param('i', $payload['user_id']);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($row && !empty($row['token_blacklisted_at'])) {
                    $blacklistedAt = strtotime($row['token_blacklisted_at']);
                    $tokenIat = $payload['iat'] ?? 0;
                    if ($tokenIat < $blacklistedAt) {
                        Response::error('Sessão expirada. Faça login novamente.', 401);
                    }
                }
            } catch (\Exception $e) {
                // Column may not exist yet - skip blacklist check
            }
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
