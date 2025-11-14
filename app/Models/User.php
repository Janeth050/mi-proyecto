<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'whatsapp_phone',     // ← nuevo
        'notify_low_stock',   // ← nuevo
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'notify_low_stock'  => 'boolean', // ← importante para el switch
        // 'password' => 'hashed', // (opcional en Laravel 10+)
    ];

    public function isAdmin(): bool
    {
        return ($this->role === 'admin') || (bool)($this->is_admin ?? false);
    }

    public function isEmpleado(): bool
    {
        return $this->role === 'empleado';
    }
}

