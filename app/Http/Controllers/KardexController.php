<?php

namespace App\Http\Controllers;

use App\Models\Kardex;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KardexController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Si quisieras obligar Gate:
        // $this->middleware('can:view-kardex')->only(['index','showProducto','kardexData']);
    }

    /**
     * Vista principal del Kardex con filtros (producto y fechas).
     * Muestra tabla cronológica: fecha, tipo, entrada, salida, saldo, costos, etc.
     */
    public function index(Request $request)
    {
        $tz        = config('app.timezone', 'America/Monterrey');
        $productos = Producto::orderBy('nombre')->get(['id','nombre']);

        $q = Kardex::with([
                'producto:id,nombre,unidad_id',
                'movimiento:id,producto_id,tipo'
            ])
            ->orderBy('fecha', 'asc');

        // Filtros amigables
        $q->when($request->filled('producto_id'), fn($qq) =>
            $qq->where('producto_id', $request->integer('producto_id'))
        );

        // Rango de fechas (interpretados en hora local)
        if ($request->filled('desde')) {
            $desde = Carbon::parse($request->string('desde'), $tz)->startOfDay();
            $q->where('fecha', '>=', $desde);
        }
        if ($request->filled('hasta')) {
            $hasta = Carbon::parse($request->string('hasta'), $tz)->endOfDay();
            $q->where('fecha', '<=', $hasta);
        }

        // Seguridad si no eligen producto (para no traer toda la historia)
        if (!$request->filled('producto_id')) {
            $q->limit(150);
        }

        $kardex = $q->get();

        return view('kardex.index', compact('kardex', 'productos'));
    }

    /**
     * Kardex de un producto específico (vista).
     */
    public function showProducto(Producto $producto, Request $request)
    {
        $tz = config('app.timezone', 'America/Monterrey');

        $q = Kardex::with(['producto:id,nombre,unidad_id', 'movimiento:id,producto_id,tipo'])
            ->where('producto_id', $producto->id)
            ->orderBy('fecha', 'asc');

        if ($request->filled('desde')) {
            $desde = Carbon::parse($request->string('desde'), $tz)->startOfDay();
            $q->where('fecha', '>=', $desde);
        }
        if ($request->filled('hasta')) {
            $hasta = Carbon::parse($request->string('hasta'), $tz)->endOfDay();
            $q->where('fecha', '<=', $hasta);
        }

        $kardex = $q->get();

        return view('kardex.show_producto', [
            'producto' => $producto->load('unidad:id,clave,nombre'),
            'kardex'   => $kardex,
        ]);
    }

    /**
     * JSON para modales/SPA: devuelve movimientos de un producto con filtros.
     * GET /kardex/data?producto_id=..&desde=YYYY-MM-DD&hasta=YYYY-MM-DD
     */
    public function kardexData(Request $request)
    {
        $tz = config('app.timezone', 'America/Monterrey');

        $request->validate([
            'producto_id' => ['required','exists:productos,id'],
            'desde'       => ['nullable','date'],
            'hasta'       => ['nullable','date'],
        ]);

        $q = Kardex::with(['movimiento:id,producto_id,tipo'])
            ->where('producto_id', (int)$request->producto_id)
            ->orderBy('fecha','asc');

        if ($request->filled('desde')) {
            $desde = Carbon::parse($request->string('desde'), $tz)->startOfDay();
            $q->where('fecha', '>=', $desde);
        }
        if ($request->filled('hasta')) {
            $hasta = Carbon::parse($request->string('hasta'), $tz)->endOfDay();
            $q->where('fecha', '<=', $hasta);
        }

        $rows = $q->get([
            'id','producto_id','movimiento_id','fecha','tipo',
            'entrada','salida','saldo','costo_unitario','costo_total'
        ]);

        // Adjuntamos un campo de solo lectura con la fecha formateada en hora local
        $mapped = $rows->map(function ($r) use ($tz) {
            return [
                'id'             => $r->id,
                'producto_id'    => $r->producto_id,
                'movimiento_id'  => $r->movimiento_id,
                'fecha'          => $r->fecha, // original (Carbon/DateTime)
                'fecha_local'    => Carbon::parse($r->fecha)->timezone($tz)->format('d/m/Y H:i'),
                'tipo'           => $r->tipo,
                'entrada'        => $r->entrada,
                'salida'         => $r->salida,
                'saldo'          => $r->saldo,
                'costo_unitario' => $r->costo_unitario,
                'costo_total'    => $r->costo_total,
            ];
        });

        return response()->json([
            'ok'   => true,
            'rows' => $mapped,
        ]);
    }
}
