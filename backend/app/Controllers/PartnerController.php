<?php

namespace App\Controllers;

class PartnerController
{
    public function index()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'partners' => []]);
    }

    public function store()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Partner created']);
    }

    public function update($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Partner updated']);
    }

    public function destroy($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Partner deleted']);
    }
}
