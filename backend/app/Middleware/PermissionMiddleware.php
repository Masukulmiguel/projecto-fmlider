<?php

namespace App\Middleware;

class PermissionMiddleware
{
    public function handle($request, $next, $roles)
    {
        $userRole = $this->getUserRole();

        if (!in_array($userRole, $roles)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit;
        }

        return $next($request);
    }

    private function getUserRole()
    {
        // Get user role from token or session
        return 'admin'; // Mock
    }
}
