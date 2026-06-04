<?php

namespace App\Models;

class Testimonial
{
    protected $table = 'testimonials';
    protected $fillable = ['name', 'position', 'company', 'message', 'photo', 'rating', 'status', 'order_by'];
}
