<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $nombres = ['Harinas','Lácteos','Azúcares','Grasas','Conservadores','Empaques'];
        foreach ($nombres as $nombre) {
            Categoria::withTrashed()->updateOrCreate(['nombre' => $nombre], ['deleted_at' => null]);
        }
    }
}

