<?php

namespace Database\Seeders;

use App\Models\Unidad;
use Illuminate\Database\Seeder;

class UnidadSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['clave' => 'costal', 'descripcion' => 'Costal'],
            ['clave' => 'caja',   'descripcion' => 'Caja'],
            ['clave' => 'pza',    'descripcion' => 'Pieza'],
            ['clave' => 'kg',     'descripcion' => 'Kilogramo'],
            ['clave' => 'lt',     'descripcion' => 'Litro'],
        ];

        foreach ($rows as $r) {
            Unidad::updateOrCreate(['clave' => $r['clave']], $r);
        }
    }
}
