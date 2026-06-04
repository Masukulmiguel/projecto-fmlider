<?php

namespace App\Controllers;

class ContactController
{
    public function index()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'contacts' => []]);
    }

    public function store()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        return json_encode(['success' => true, 'message' => 'Contact message received']);
    }

    public function show($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'contact' => []]);
    }

    public function markAsRead($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Marked as read']);
    }

    public function reply($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Reply sent']);
    }

    public function destroy($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Contact deleted']);
    }
}
