<?php

namespace App\Controllers;

class ServiceController
{
    public function index()
    {
        header('Content-Type: application/json');
        return json_encode([
            'success' => true,
            'services' => [
                ['id' => 1, 'title' => 'Desembaraço Aduaneiro', 'slug' => 'desembaraco-aduaneiro'],
                ['id' => 2, 'title' => 'Transportes', 'slug' => 'transportes'],
                ['id' => 3, 'title' => 'Armazenagem', 'slug' => 'armazenagem'],
                ['id' => 4, 'title' => 'Door To Door', 'slug' => 'door-to-door']
            ]
        ]);
    }

    public function store()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        return json_encode(['success' => true, 'message' => 'Service created']);
    }

    public function show($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'service' => ['id' => $id]]);
    }

    public function update($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Service updated']);
    }

    public function destroy($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Service deleted']);
    }
}
