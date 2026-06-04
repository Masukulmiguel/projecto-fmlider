<?php

namespace App\Models;

class Gallery
{
    protected $table = 'gallery';
    protected $fillable = ['title', 'image', 'category', 'description', 'alt_text', 'order_by', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
