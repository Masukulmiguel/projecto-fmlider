<?php

namespace App\Controllers;

class TestimonialController
{
    public function index()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'testimonials' => []]);
    }

    public function store()
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Testimonial created']);
    }

    public function update($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Testimonial updated']);
    }

    public function destroy($id)
    {
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Testimonial deleted']);
    }
}
