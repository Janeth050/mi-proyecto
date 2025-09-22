<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Si ya existe el correo, lo actualiza; si no, lo crea.
        User::updateOrCreate(
            ['email' => 'admin@panaderia.local'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Admin123*'), // SIEMPRE con hash
                'role' => 'admin',
            ]
        );
    }
}
