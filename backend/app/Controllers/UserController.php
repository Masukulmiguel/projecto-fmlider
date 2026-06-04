<?php

namespace App\Controllers;

class UserController
{
    public function index()
    {
        header('Content-Type: application/json');
        return json_encode([
            'success' => true,
            'users' => [
                ['id' => 1, 'name' => 'Admin User', 'email' => 'admin@fmlider.co.ao', 'role' => 'admin', 'status' => 1],
                ['id' => 2, 'name' => 'Editor User', 'email' => 'editor@fmlider.co.ao', 'role' => 'editor', 'status' => 1]
            ]
        ]);
    }

    public function store()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        return json_encode(['success' => true, 'message' => 'User created', 'user' => $data]);
    }

    public function show($id)
    {
        header('Content-Type: application/json');
        return json_encode([
            'success' => true,
            'user' => ['id' => $id, 'name' => 'User Name', 'email' => 'user@example.com']
        ]);
    }

    public function update($id)
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        return json_encode(['success' => true, 'message' => 'User updated']);
    }

    public function destroy($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'User deleted']);
    }
}
