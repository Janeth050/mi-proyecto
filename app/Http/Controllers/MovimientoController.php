<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovimientoController extends Controller
{
    /**
     * Mostrar todos los movimientos (entradas/salidas)
     */
    public function index()
    {
        // Trae los movimientos con la información relacionada
        $movimientos = Movimiento::with(['producto', 'usuario', 'proveedor'])
            ->orderByDesc('created_at')
            ->get();

        return view('movimientos.index', compact('movimientos'));
    }

    /**
     * Formulario para registrar un nuevo movimiento
     */
    public function create()
    {
        $productos = Producto::all();
        $proveedors = Proveedor::all();

        return view('movimientos.create', compact('productos', 'proveedors'));
    }

    /**
     * Guardar el nuevo movimiento en la base de datos
     */
    public function store(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'tipo' => 'required|in:entrada,salida',
            'cantidad' => 'required|integer|min:1',
            'descripcion' => 'nullable|string',
            'proveedor_id' => 'nullable|exists:proveedors,id',
            'costo_unitario' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $producto = Producto::findOrFail($request->producto_id);

            // Calcula las existencias después del movimiento
            $cantidad = (int) $request->cantidad;
            $existencias = $producto->existencias;

            if ($request->tipo === 'entrada') {
                $existencias += $cantidad;
            } elseif ($request->tipo === 'salida') {
                // Verifica que haya stock suficiente
                if ($existencias < $cantidad) {
                    throw new \Exception("No hay suficientes existencias para realizar la salida.");
                }
                $existencias -= $cantidad;
            }

            // Guarda el movimiento
            $movimiento = Movimiento::create([
                'producto_id' => $producto->id,
                'user_id' => Auth::id(),
                'tipo' => $request->tipo,
                'cantidad' => $cantidad,
                'descripcion' => $request->descripcion,
                'proveedor_id' => $request->proveedor_id,
                'costo_unitario' => $request->costo_unitario,
                'costo_total' => $request->costo_unitario ? $request->costo_unitario * $cantidad : null,
                'status' => 'confirmado',
                'existencias_despues' => $existencias,
            ]);

            // Actualiza el stock del producto
            $producto->update(['existencias' => $existencias]);
        });

        return redirect()->route('movimientos.index')->with('success', 'Movimiento registrado correctamente.');
    }

    /**
     * Mostrar detalles del movimiento (opcional)
     */
    public function show(Movimiento $movimiento)
    {
        return view('movimientos.show', compact('movimiento'));
    }
}
