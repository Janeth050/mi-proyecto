<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kardex extends Model
{
    use HasFactory;

    protected $table = 'kardex';

    protected $fillable = [
        'producto_id','movimiento_id','fecha',
        'tipo','entrada','salida','saldo',
        'costo_unitario','costo_total',
    ];

    protected $casts = [
        'fecha'         => 'datetime',
        'entrada'       => 'integer',
        'salida'        => 'integer',
        'saldo'         => 'integer',
        'costo_unitario'=> 'decimal:4',
        'costo_total'   => 'decimal:4',
    ];

    public function producto()  { return $this->belongsTo(Producto::class, 'producto_id'); }
    public function movimiento(){ return $this->belongsTo(Movimiento::class, 'movimiento_id'); }
}
