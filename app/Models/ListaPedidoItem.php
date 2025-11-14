<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListaPedidoItem extends Model
{
    use HasFactory;

    protected $table = 'lista_pedido_items';

    protected $fillable = ['lista_pedido_id','producto_id','cantidad','proveedor_id','precio_estimado'];

    protected $casts = [
        'cantidad'        => 'integer',
        'precio_estimado' => 'decimal:2',
    ];

    public function lista()     { return $this->belongsTo(ListaPedido::class, 'lista_pedido_id'); }
    public function producto()  { return $this->belongsTo(Producto::class, 'producto_id'); }
    public function proveedor() { return $this->belongsTo(Proveedor::class, 'proveedor_id'); }
}
