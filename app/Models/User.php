<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = ['name','email','password','role'];

    protected $hidden = ['password','remember_token']; 

    protected $casts = [
        'email_verified_at' => 'datetime',
        // Si usas Laravel 10+, puedes activar hash automático:
        // 'password' => 'hashed',
    ];

    public function isAdmin(): bool    { return ($this->role === 'admin') || (bool)($this->is_admin ?? false); }
    public function isEmpleado(): bool { return $this->role === 'empleado'; }
}

