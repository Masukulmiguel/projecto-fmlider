<?php

namespace App\Models;

class Service
{
    protected $table = 'services';
    protected $fillable = ['title', 'slug', 'description', 'image', 'content', 'status', 'order_by'];
}
