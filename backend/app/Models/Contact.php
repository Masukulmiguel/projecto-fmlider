<?php

namespace App\Models;

class Contact
{
    protected $table = 'contacts';
    protected $fillable = ['name', 'company', 'phone', 'email', 'subject', 'message', 'is_read', 'replied_at', 'reply_message'];
}
