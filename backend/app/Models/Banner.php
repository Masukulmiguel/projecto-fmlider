<?php

namespace App\Models;

class Banner
{
    protected $table = 'banners';
    protected $fillable = ['title', 'description', 'image', 'link', 'status', 'order_by'];
}
