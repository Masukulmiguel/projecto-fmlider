<?php

namespace App\Controllers;

class FAQController
{
    public function index()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'faqs' => []]);
    }

    public function store()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'FAQ created']);
    }

    public function update($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'FAQ updated']);
    }

    public function destroy($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'FAQ deleted']);
    }
}
