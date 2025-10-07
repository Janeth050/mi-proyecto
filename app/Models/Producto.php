<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'codigo','nombre','unidad_id','categoria_id',
        'existencias','stock_minimo','costo_promedio',
        'presentacion_detalle','descripcion',
    ];

    protected $casts = [
        'existencias'      => 'integer',
        'stock_minimo'     => 'integer',
        'costo_promedio'   => 'decimal:4',
    ];

    // Relaciones
    public function unidad()     { return $this->belongsTo(Unidad::class, 'unidad_id'); }
    public function categoria()  { return $this->belongsTo(Categoria::class, 'categoria_id'); }
    public function movimientos(){ return $this->hasMany(Movimiento::class, 'producto_id'); }
    public function kardex()     { return $this->hasMany(Kardex::class, 'producto_id'); }
    public function listaItems() { return $this->hasMany(ListaPedidoItem::class, 'producto_id'); }

    // Scopes / helpers
    public function scopeBajoStock($q)
    {
        return $q->whereColumn('existencias','<','stock_minimo');
    }

    public function getEnAlertaAttribute(): bool
    {
        return (int)$this->existencias < (int)$this->stock_minimo;
    }

    public function getEtiquetaAttribute(): string
    {
        // p.ej: "Harina Costal 50 kg (costal) — x3"
        $u = $this->unidad?->clave;
        $det = $this->presentacion_detalle ? " {$this->presentacion_detalle}" : '';
        return "{$this->nombre}{$det}" . ($u ? " ({$u})" : '');
    }
}
