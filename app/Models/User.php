<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role','is_admin'
    ];

    protected $hidden = ['password', 'remember_token'];

    // Método para verificar si es admin
    public function is_admin(): bool
    {
        return $this->role === 'admin';
    }
}