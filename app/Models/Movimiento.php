<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        'cantidad'            => 'integer',
        'existencias_despues' => 'integer',
        'costo_unitario'      => 'decimal:4',
        'costo_total'         => 'decimal:4',
        'confirmado_en'       => 'datetime',
    ];

    // ================= Relaciones =================
    public function producto()   { return $this->belongsTo(Producto::class, 'producto_id'); }
    public function usuario()    { return $this->belongsTo(User::class, 'user_id'); }
    public function proveedor()  { return $this->belongsTo(Proveedor::class, 'proveedor_id'); }
    public function confirmador(){ return $this->belongsTo(User::class, 'confirmado_por'); }

    // ================= Helpers cortos =================
    public function esEntrada(): bool { return $this->tipo === 'entrada'; }
    public function esSalida(): bool  { return $this->tipo === 'salida';  }
    public function confirmado(): bool{ return $this->status === 'confirmado'; }

    public $timestamps = true;

    // ============================================================
    // SERVICIO: registrar un movimiento (+ actualizar stock + kardex)
    // Se usa desde 'cerrar' lista para crear ENTRADAS por cada ítem.
    // ============================================================
    public static function registrar(array $data): self
    {
        /**
         * $data esperado:
         * - producto_id (int)  OBLIGATORIO
         * - tipo ('entrada'|'salida') OBLIGATORIO
         * - cantidad (int)     OBLIGATORIO
         * - proveedor_id (int|null)
         * - costo_unitario (float|null)
         * - descripcion (string|null)
         * - user_id (int|null)  (si no viene, se usa Auth::id())
         */
        return DB::transaction(function () use ($data) {
            $productoId    = (int) $data['producto_id'];
            $tipo          = $data['tipo'];
            $cantidad      = (int) $data['cantidad'];
            $proveedorId   = $data['proveedor_id'] ?? null;
            $costoUnitario = $data['costo_unitario'] ?? null;
            $descripcion   = $data['descripcion']   ?? null;
            $userId        = $data['user_id']       ?? Auth::id();

            // Bloqueamos el producto para cálculo de existencias coherente
            $producto    = Producto::lockForUpdate()->findOrFail($productoId);
            $existencias = (int) $producto->existencias;

            if ($tipo === 'entrada') {
                $existencias += $cantidad;
            } else { // salida
                if ($existencias < $cantidad) {
                    throw new \Exception("No hay suficientes existencias para realizar la salida.");
                }
                $existencias -= $cantidad;
            }

            // Creamos el movimiento ya en estado 'confirmado'
            $mov = self::create([
                'producto_id'         => $producto->id,
                'user_id'             => $userId,
                'tipo'                => $tipo,
                'cantidad'            => $cantidad,
                'descripcion'         => $descripcion,
                'proveedor_id'        => $proveedorId,
                'costo_unitario'      => $costoUnitario,
                'costo_total'         => $costoUnitario ? $costoUnitario * $cantidad : null,
                'status'              => 'confirmado',
                'existencias_despues' => $existencias,
            ]);

            // Actualizamos el stock del producto
            $producto->update(['existencias' => $existencias]);

            // Asiento Kardex
            Kardex::create([
                'producto_id'    => $producto->id,
                'movimiento_id'  => $mov->id,
                'fecha'          => now(),
                'tipo'           => $mov->tipo,
                'entrada'        => $mov->tipo === 'entrada' ? $cantidad : 0,
                'salida'         => $mov->tipo === 'salida'  ? $cantidad : 0,
                'saldo'          => $existencias,
                'costo_unitario' => $mov->costo_unitario,
                'costo_total'    => $mov->costo_total,
            ]);

            return $mov;
        });
    }

    // ============================================================
    // Helper específico: registrar ENTRADA proveniente del cierre de una lista
    // Usa precio_estimado (si viene) como costo_unitario y agrega una nota.
    // ============================================================
    public static function registrarEntradaDesdeLista(int $productoId, int $cantidad, ?int $proveedorId = null, $precioEstimado = null, ?int $userId = null): self
    {
        return self::registrar([
            'producto_id'    => $productoId,
            'tipo'           => 'entrada',
            'cantidad'       => $cantidad,
            'proveedor_id'   => $proveedorId,
            'costo_unitario' => $precioEstimado,              // si no hay, se queda null
            'descripcion'    => 'Entrada por cierre de lista',
            'user_id'        => $userId ?? Auth::id(),
        ]);
    }
}
