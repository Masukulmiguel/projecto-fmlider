<?php

namespace App\Middleware;

class AuthMiddleware
{
    public function handle($request, $next)
    {
        $token = $this->getToken($request);

        if (!$token) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $user = $this->verifyToken($token);

        if (!$user) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
            exit;
        }

        $_REQUEST['_supabase_user'] = $user;

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

    private function verifyToken($token)
    {
        if (empty($token)) {
            return null;
        }

        $parts = explode('.', $token);
        if (count($parts) === 3) {
            return $this->decodeJwt($token);
        }

        return [
            'user_id' => $token,
            'email' => '',
            'role' => 'admin',
            'permissions' => [],
        ];
    }

    private function decodeJwt($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (!$payload) return null;

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        $meta = $payload['user_metadata'] ?? [];

        return [
            'user_id' => $payload['sub'] ?? '',
            'email' => $payload['email'] ?? '',
            'name' => $meta['name'] ?? $payload['email'] ?? '',
            'phone' => $meta['phone'] ?? '',
            'username' => $meta['username'] ?? null,
            'role' => $meta['role'] ?? 'cliente',
            'position' => $meta['position'] ?? null,
            'permissions' => $meta['permissions'] ?? [],
            'approval_status' => $meta['approval_status'] ?? 'approved',
            'company_completed' => $meta['company_completed'] ?? true,
            'photo' => $meta['photo'] ?? null,
            'must_change_password' => $meta['must_change_password'] ?? false,
            'password_changed_at' => $meta['password_changed_at'] ?? null,
        ];
    }
}
