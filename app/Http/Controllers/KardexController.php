<?php

namespace App\Http\Controllers;

use App\Models\Kardex;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class KardexController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // La ruta ya tiene can:view-kardex; si quisieras forzarlo aquí:
        // $this->middleware('can:view-kardex')->only(['index','showProducto','kardexData']);
    }

    /**
     * Vista principal del Kardex con filtros (producto y fechas).
     * Muestra tabla cronológica: fecha, tipo, entrada, salida, saldo, costos, etc.
     */
    public function index(Request $request)
    {
        $productos = Producto::orderBy('nombre')->get();

        $q = Kardex::with(['producto', 'movimiento'])
            ->orderBy('fecha', 'asc');

        // Filtro por producto
        if ($request->filled('producto_id')) {
            $q->where('producto_id', $request->producto_id);
        }

        // Rango de fechas (hasta = fin del día)
        if ($request->filled('desde')) {
            $q->where('fecha', '>=', Carbon::parse($request->desde)->startOfDay());
        }
        if ($request->filled('hasta')) {
            $q->where('fecha', '<=', Carbon::parse($request->hasta)->endOfDay());
        }

        // Protección si no se seleccionó producto
        if (!$request->filled('producto_id')) {
            $q->limit(100);
        }

        $kardex = $q->get();

        return view('kardex.index', compact('kardex', 'productos'));
    }

    /**
     * Kardex de un producto específico (vista).
     */
    public function showProducto(Producto $producto, Request $request)
    {
        $q = Kardex::with(['producto', 'movimiento'])
            ->where('producto_id', $producto->id)
            ->orderBy('fecha', 'asc');

        if ($request->filled('desde')) {
            $q->where('fecha', '>=', Carbon::parse($request->desde)->startOfDay());
        }
        if ($request->filled('hasta')) {
            $q->where('fecha', '<=', Carbon::parse($request->hasta)->endOfDay());
        }

        $kardex = $q->get();

        return view('kardex.show_producto', [
            'producto' => $producto->load('unidad'),
            'kardex'   => $kardex,
        ]);
    }

    /**
     * JSON para modales/SPA: devuelve movimientos de un producto con filtros.
     * GET /kardex/data?producto_id=..&desde=YYYY-MM-DD&hasta=YYYY-MM-DD
     */
    public function kardexData(Request $request)
    {
        // Gate opcional: $this->authorize('view-kardex');

        $request->validate([
            'producto_id' => ['required','exists:productos,id'],
            'desde'       => ['nullable','date'],
            'hasta'       => ['nullable','date'],
        ]);

        $q = Kardex::with(['movimiento'])
            ->where('producto_id', $request->producto_id)
            ->orderBy('fecha','asc');

        if ($request->filled('desde')) {
            $q->where('fecha', '>=', Carbon::parse($request->desde)->startOfDay());
        }
        if ($request->filled('hasta')) {
            $q->where('fecha', '<=', Carbon::parse($request->hasta)->endOfDay());
        }

        $rows = $q->get([
            'id','producto_id','movimiento_id','fecha','tipo',
            'entrada','salida','saldo','costo_unitario','costo_total'
        ]);

        return response()->json([
            'ok'      => true,
            'rows'    => $rows,
        ]);
    }
}
