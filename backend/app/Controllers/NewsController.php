<?php

namespace App\Controllers;

class NewsController
{
    public function index()
    {
        header('Content-Type: application/json');
        return json_encode([
            'success' => true,
            'news' => []
        ]);
    }

    public function store()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'News created']);
    }

    public function show($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'news' => []]);
    }

    public function update($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'News updated']);
    }

    public function destroy($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'News deleted']);
    }
}
