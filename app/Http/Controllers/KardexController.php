<?php

namespace App\Http\Controllers;

use App\Models\Kardex;
use App\Models\Producto;
use Illuminate\Http\Request;

class KardexController extends Controller
{
    /**
     * Vista principal del Kardex con filtros (producto y fechas).
     * Muestra tabla cronológica: fecha, tipo, entrada, salida, saldo, costos, etc.
     */
    public function index(Request $request)
    {
        // Para el filtro de producto (desplegable)
        $productos = Producto::orderBy('nombre')->get();

        // Construimos la consulta base
        $q = Kardex::with(['producto', 'movimiento'])
            ->orderBy('fecha', 'asc'); // El Kardex se lee cronológicamente

        // Filtro por producto (recomendado)
        if ($request->filled('producto_id')) {
            $q->where('producto_id', $request->producto_id);
        }

        // Filtro por fecha inicial
        if ($request->filled('desde')) {
            $q->whereDate('fecha', '>=', $request->desde);
        }

        // Filtro por fecha final
        if ($request->filled('hasta')) {
            $q->whereDate('fecha', '<=', $request->hasta);
        }

        // Si no filtraste producto, no saturamos. Puedes comentar este if si quieres ver todo.
        if (!$request->filled('producto_id')) {
            $q->limit(100); // protección básica; puedes quitarlo
        }

        // Traemos resultados (puedes usar paginate si prefieres)
        $kardex = $q->get();

        return view('kardex.index', compact('kardex', 'productos'));
    }

    /**
     * Kardex de un producto específico.
     * Útil si pones un botón "Ver Kardex" desde la lista de productos.
     */
    public function showProducto(Producto $producto, Request $request)
    {
        $q = Kardex::with(['producto', 'movimiento'])
            ->where('producto_id', $producto->id)
            ->orderBy('fecha', 'asc');

        if ($request->filled('desde')) {
            $q->whereDate('fecha', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $q->whereDate('fecha', '<=', $request->hasta);
        }

        $kardex = $q->get();

        return view('kardex.show_producto', [
            'producto' => $producto,
            'kardex'   => $kardex,
        ]);
    }
}
