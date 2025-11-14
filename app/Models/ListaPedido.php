<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListaPedido extends Model
{
    use HasFactory;

    protected $table = 'listas_pedido';

    protected $fillable = ['user_id','status','comentario'];

    public function creador() { return $this->belongsTo(User::class, 'user_id'); }
    public function items()   { return $this->hasMany(ListaPedidoItem::class, 'lista_pedido_id'); }

    // Total estimado (precio_estimado * cantidad)
    public function getTotalEstimadoAttribute(): float
    {
        return (float) (
            $this->items()
                ->selectRaw('SUM(COALESCE(precio_estimado,0) * cantidad) AS total')
                ->value('total') ?? 0
        );
    }

    // ===== "Modo ligero": helpers
    public function scopeActivaDe($q, int $userId)
    {
        return $q->where('user_id', $userId)->where('status', 'borrador');
    }

    public static function activaOrCreateFor(int $userId): self
    {
        $lista = static::where('user_id', $userId)->where('status', 'borrador')->first();
        if ($lista) return $lista;

        return static::create([
            'user_id'    => $userId,
            'status'     => 'borrador',
            'comentario' => 'Lista de reposición',
        ]);
    }
}
