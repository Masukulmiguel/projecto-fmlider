<?php

namespace App\Models;

class News
{
    protected $table = 'news';
    protected $fillable = ['title', 'slug', 'image', 'description', 'content', 'category', 'status', 'user_id', 'published_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
