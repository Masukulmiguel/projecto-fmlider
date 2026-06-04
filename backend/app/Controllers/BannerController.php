<?php

namespace App\Controllers;

class BannerController
{
    public function index()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'banners' => []]);
    }

    public function store()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Banner created']);
    }

    public function update($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Banner updated']);
    }

    public function destroy($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Banner deleted']);
    }
}
