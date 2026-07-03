<?php

namespace App\Middleware;

use App\Helpers\Jwt;

class AuthMiddleware
{
    public function handle($request, $next)
    {
        $token = $this->getToken($request);

        if (!$token) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $payload = Jwt::decode($token);

        if (!$payload) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $meta = $payload['user_metadata'] ?? [];

        $_REQUEST['_supabase_user'] = [
            'user_id' => $payload['sub'] ?? '',
            'email' => $payload['email'] ?? '',
            'name' => $meta['name'] ?? $payload['email'] ?? '',
            'phone' => $meta['phone'] ?? '',
            'username' => $meta['username'] ?? null,
            'role' => $meta['role'] ?? 'cliente',
            'position' => $meta['position'] ?? null,
            'permissions' => $meta['permissions'] ?? [],
            'approval_status' => $meta['approval_status'] ?? 'pending',
            'company_completed' => $meta['company_completed'] ?? false,
            'photo' => $meta['photo'] ?? null,
            'must_change_password' => $meta['must_change_password'] ?? false,
            'password_changed_at' => $meta['password_changed_at'] ?? null,
        ];

        return $next($request);
    }

    private function getToken($request)
    {
        $header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
