<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;

    protected $table = 'movimientos';

    protected $fillable = [
        'producto_id','user_id','tipo','cantidad','descripcion',
        'proveedor_id','costo_unitario','costo_total',
        'status','confirmado_por','confirmado_en',
        'existencias_despues',
    ];

    protected $casts = [
        'cantidad'           => 'integer',     
        'existencias_despues'=> 'integer',
        'costo_unitario'     => 'decimal:4',
        'costo_total'        => 'decimal:4',
        'confirmado_en'      => 'datetime',
    ];

    // Relaciones
    public function producto()   { return $this->belongsTo(Producto::class, 'producto_id'); }
    public function usuario()    { return $this->belongsTo(User::class, 'user_id'); }
    public function proveedor()  { return $this->belongsTo(Proveedor::class, 'proveedor_id'); }
    public function confirmador(){ return $this->belongsTo(User::class, 'confirmado_por'); }

    // Helpers
    public function esEntrada(): bool { return $this->tipo === 'entrada'; }
    public function esSalida(): bool  { return $this->tipo === 'salida';  }
    public function confirmado(): bool{ return $this->status === 'confirmado'; }
    
    public $timestamps = true;
}
