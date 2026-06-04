<?php

namespace App\Models;

class User
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'phone', 'role', 'password', 'photo', 'status'];
    protected $hidden = ['password'];

    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function news()
    {
        return $this->hasMany(News::class, 'user_id');
    }

    public function galleries()
    {
        return $this->hasMany(Gallery::class, 'user_id');
    }
}
