<?php

namespace App\Models;

class FAQ
{
    protected $table = 'faqs';
    protected $fillable = ['question', 'answer', 'category', 'order_by', 'status'];
}
