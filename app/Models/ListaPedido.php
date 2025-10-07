<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListaPedido extends Model
{
    use HasFactory;

    protected $table = 'listas_pedido'; // importante

    protected $fillable = ['user_id','status','comentario'];

    public function creador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(ListaPedidoItem::class, 'lista_pedido_id');
    }

    // Suma rápida (si hay precio estimado) - SIN DB::raw
    public function getTotalEstimadoAttribute(): float
    {
        return (float) (
            $this->items()
                ->selectRaw('SUM(COALESCE(precio_estimado,0) * cantidad) AS total')
                ->value('total') ?? 0
        );
    }
}
