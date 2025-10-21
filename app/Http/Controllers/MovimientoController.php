<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Kardex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovimientoController extends Controller
{
    /**
     * LISTADO con filtros y paginación
     */
    public function index(Request $request)
    {
        $movimientos = Movimiento::with(['producto.unidad','usuario','proveedor'])
            ->when($request->filled('tipo'), fn($q)=> $q->where('tipo',$request->tipo))
            ->when($request->filled('status'), fn($q)=> $q->where('status',$request->status))
            ->when($request->filled('q'), function($q) use ($request){
                $q->where(function($qq) use ($request){
                    $qq->whereHas('producto', fn($p)=> $p->where('nombre','like','%'.$request->q.'%'))
                       ->orWhereHas('usuario', fn($u)=> $u->where('name','like','%'.$request->q.'%'));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20) // 👈 importante para filtros + rendimiento
            ->appends($request->query());

        return view('movimientos.index', compact('movimientos'));
    }

    /**
     * FORM crear
     */
    public function create()
    {
        $productos  = Producto::orderBy('nombre')->get();
        $proveedors = Proveedor::orderBy('nombre')->get();
        return view('movimientos.create', compact('productos','proveedors'));
    }

    /**
     * GUARDAR (afecta stock + kardex)
     */
    public function store(Request $request)
    {
        $request->validate([
            'producto_id'    => 'required|exists:productos,id',
            'tipo'           => 'required|in:entrada,salida',
            'cantidad'       => 'required|integer|min:1',
            'descripcion'    => 'nullable|string',
            'proveedor_id'   => 'nullable|exists:proveedors,id',
            'costo_unitario' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $producto    = Producto::lockForUpdate()->findOrFail($request->producto_id);
            $cantidad    = (int) $request->cantidad;
            $existencias = $producto->existencias;

            if ($request->tipo === 'entrada') {
                $existencias += $cantidad;
            } else {
                if ($existencias < $cantidad) {
                    throw new \Exception("No hay suficientes existencias para realizar la salida.");
                }
                $existencias -= $cantidad;
            }

            $mov = Movimiento::create([
                'producto_id'         => $producto->id,
                'user_id'             => Auth::id(),
                'tipo'                => $request->tipo,
                'cantidad'            => $cantidad,
                'descripcion'         => $request->descripcion,
                'proveedor_id'        => $request->proveedor_id,
                'costo_unitario'      => $request->costo_unitario,
                'costo_total'         => $request->costo_unitario ? $request->costo_unitario * $cantidad : null,
                'status'              => 'confirmado',   // hoy confirmamos al guardar
                'existencias_despues' => $existencias,
            ]);

            // Actualiza stock
            $producto->update(['existencias' => $existencias]);

            // Kardex
            Kardex::create([
                'producto_id'   => $producto->id,
                'movimiento_id' => $mov->id,
                'fecha'         => now(),
                'tipo'          => $mov->tipo,
                'entrada'       => $mov->tipo === 'entrada' ? $cantidad : 0,
                'salida'        => $mov->tipo === 'salida'  ? $cantidad : 0,
                'saldo'         => $existencias,
                'costo_unitario'=> $mov->costo_unitario,
                'costo_total'   => $mov->costo_total,
            ]);
        });

        return redirect()->route('movimientos.index')->with('success', 'Movimiento registrado correctamente.');
    }

    /**
     * CANCELAR (recomendada en vez de editar/borrar confirmados)
     * - Cambia status a 'cancelado'
     * - Revierte el stock
     * - Inserta un kardex de reverso
     */
    public function cancelar(Movimiento $movimiento)
    {
        if ($movimiento->status === 'cancelado') {
            return back()->with('error', 'El movimiento ya estaba cancelado.');
        }

        DB::transaction(function () use ($movimiento) {
            $producto    = Producto::lockForUpdate()->findOrFail($movimiento->producto_id);
            $cantidad    = (int) $movimiento->cantidad;

            // Reverso de existencias
            if ($movimiento->tipo === 'entrada') {
                // La entrada sumó, al cancelar restamos
                $nuevoSaldo = $producto->existencias - $cantidad;
                if ($nuevoSaldo < 0) { // protección
                    throw new \Exception('No es posible cancelar: dejaría existencias negativas.');
                }
                $producto->update(['existencias' => $nuevoSaldo]);

                // Kardex reverso (salida)
                Kardex::create([
                    'producto_id'   => $producto->id,
                    'movimiento_id' => $movimiento->id,
                    'fecha'         => now(),
                    'tipo'          => 'salida',
                    'entrada'       => 0,
                    'salida'        => $cantidad,
                    'saldo'         => $nuevoSaldo,
                    'costo_unitario'=> $movimiento->costo_unitario,
                    'costo_total'   => $movimiento->costo_unitario ? $movimiento->costo_unitario * $cantidad : null,
                ]);
            } else {
                // La salida restó, al cancelar sumamos
                $nuevoSaldo = $producto->existencias + $cantidad;
                $producto->update(['existencias' => $nuevoSaldo]);

                // Kardex reverso (entrada)
                Kardex::create([
                    'producto_id'   => $producto->id,
                    'movimiento_id' => $movimiento->id,
                    'fecha'         => now(),
                    'tipo'          => 'entrada',
                    'entrada'       => $cantidad,
                    'salida'        => 0,
                    'saldo'         => $nuevoSaldo,
                    'costo_unitario'=> $movimiento->costo_unitario,
                    'costo_total'   => $movimiento->costo_unitario ? $movimiento->costo_unitario * $cantidad : null,
                ]);
            }

            // Marca cancelado
            $movimiento->update(['status' => 'cancelado']);
        });

        return back()->with('success', 'Movimiento cancelado y stock revertido.');
    }

    /**
     * (Opcional) ELIMINAR registro:
     * - Solo si ya está cancelado (no mueve stock)
     * - Sirve para "limpieza" de registros cancelados
     */
    public function destroy(Movimiento $movimiento)
    {
        if ($movimiento->status !== 'cancelado') {
            return back()->with('error','Solo puedes eliminar movimientos cancelados (para evitar desbalanceo).');
        }

        DB::transaction(function () use ($movimiento) {
            // Borra kardex asociado a este movimiento
            Kardex::where('movimiento_id', $movimiento->id)->delete();
            $movimiento->delete();
        });

        return back()->with('success','Movimiento eliminado.');
    }
}
