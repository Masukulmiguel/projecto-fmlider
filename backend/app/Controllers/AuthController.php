<?php

namespace App\Controllers;

class AuthController
{
    public function login()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['email'], $data['password'])) {
            return json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }

        // Database query would go here
        // For now returning mock response
        return json_encode([
            'success' => true,
            'user' => [
                'id' => 1,
                'email' => $data['email'],
                'name' => 'Admin User',
                'role' => 'admin'
            ],
            'token' => 'mock_token_' . bin2hex(random_bytes(16))
        ]);
    }

    public function logout()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Logged out successfully']);
    }

    public function register()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['name'], $data['email'], $data['password'])) {
            return json_encode(['success' => false, 'message' => 'Invalid data']);
        }

        return json_encode(['success' => true, 'message' => 'User registered']);
    }

    public function refreshToken()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'token' => 'new_token']);
    }

    public function resetPassword()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        return json_encode(['success' => true, 'message' => 'Password reset link sent']);
    }

    public function changePassword()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        return json_encode(['success' => true, 'message' => 'Password changed']);
    }

    public function getProfile()
    {
        header('Content-Type: application/json');
        return json_encode([
            'success' => true,
            'user' => [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@fmlider.co.ao',
                'phone' => '+244 935141747',
                'role' => 'admin',
                'photo' => null,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    public function updateProfile()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        return json_encode(['success' => true, 'message' => 'Profile updated']);
    }
}
