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

        // Validate token
        if (!$this->validateToken($token)) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }

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

    private function validateToken($token)
    {
        // Token validation logic
        return !empty($token);
    }
}
