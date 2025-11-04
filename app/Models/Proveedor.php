<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proveedors'; 

    protected $fillable = ['nombre','telefono','correo','direccion','notas'];

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'proveedor_id');
    }

    public function listaItems()
    {
        return $this->hasMany(ListaPedidoItem::class, 'proveedor_id');
    }
}
