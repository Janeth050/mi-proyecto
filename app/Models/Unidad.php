<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    use HasFactory;

    protected $table = 'unidades';

    protected $fillable = ['clave','descripcion'];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'unidad_id');
    }
}
