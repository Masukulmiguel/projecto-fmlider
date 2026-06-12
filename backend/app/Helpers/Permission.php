<?php

namespace App\Helpers;

class Permission
{
    public static function user($payload)
    {
        if (!$payload) return null;
        $appRole = $payload['user_metadata']['role'] ?? $payload['role'] ?? '';
        if ($appRole === 'admin') return $payload;
        $perms = $payload['user_metadata']['permissions'] ?? $payload['permissions'] ?? [];
        if (is_string($perms)) {
            $decoded = json_decode($perms, true);
            $perms = is_array($decoded) ? $decoded : [];
        }
        $payload['permissions'] = $perms;
        return $payload;
    }

    public static function has($payload, $permission)
    {
        $p = self::user($payload);
        if (!$p) return false;
        $appRole = $p['user_metadata']['role'] ?? $p['role'] ?? '';
        if ($appRole === 'admin') return true;
        $perms = $p['permissions'] ?? [];
        return in_array($permission, $perms, true);
    }

    public static function hasAny($payload, array $permissions)
    {
        foreach ($permissions as $perm) {
            if (self::has($payload, $perm)) return true;
        }
        return false;
    }

    public static function require($payload, $permission)
    {
        if (!self::has($payload, $permission)) {
            Response::error('Sem permissão: ' . $permission, 403);
        }
    }
}
