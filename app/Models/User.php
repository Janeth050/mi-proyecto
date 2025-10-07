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
    ];

    // Helpers de rol
    public function isAdmin(): bool   { return $this->role === 'admin'; }
    public function isEmpleado(): bool{ return $this->role === 'empleado'; }

    // Si tienes la relación con movimientos
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'user_id');
    }
}
