<?php

namespace App\Controllers;

class GalleryController
{
    public function index()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'gallery' => []]);
    }

    public function store()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Gallery item created']);
    }

    public function destroy($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Gallery item deleted']);
    }

    public function upload()
    {
        header('Content-Type: application/json');
        if (!isset($_FILES['image'])) {
            return json_encode(['success' => false, 'message' => 'No image uploaded']);
        }
        return json_encode(['success' => true, 'path' => '/uploads/image.jpg']);
    }
}
