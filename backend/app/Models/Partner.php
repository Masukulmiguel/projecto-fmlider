<?php

namespace App\Models;

class Partner
{
    protected $table = 'partners';
    protected $fillable = ['name', 'logo', 'website', 'description', 'order_by', 'status'];
}
