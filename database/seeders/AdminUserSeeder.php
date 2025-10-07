<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@panaderia.local'],
            [
                'name'              => 'Administrador',
                'password'          => Hash::make('Admin123*'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
