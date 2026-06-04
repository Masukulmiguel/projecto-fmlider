<?php

namespace App\Models;

class ActivityLog
{
    protected $table = 'activity_logs';
    protected $fillable = ['user_id', 'action', 'description', 'ip_address', 'user_agent'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
