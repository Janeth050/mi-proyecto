<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Movimiento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $u = Auth::user();
        return ($u && $u->role === 'admin') ? $this->admin() : $this->empleado();
    }

    public function admin()
    {
        // Existencias bajas
        $bajo = Producto::whereColumn('existencias','<','stock_minimo')
            ->with('unidad')->orderBy('nombre')->take(8)->get();

        // Últimos movimientos (trae 'producto' si existe la relación)
        $ultimos = Movimiento::with(['producto.unidad','usuario'])
            ->orderByDesc('created_at')->take(10)->get();

        // KPIs
        $resumen = [
            'productos_total' => Producto::count(),
            'bajo_stock'      => Producto::whereColumn('existencias','<','stock_minimo')->count(),
            'entradas_hoy'    => Movimiento::where('tipo','entrada')->whereDate('created_at', now())->count(),
            'salidas_hoy'     => Movimiento::where('tipo','salida' )->whereDate('created_at', now())->count(),
        ];

        // ===== TOP usados 30 días (salidas) =====
        $col = Schema::hasColumn('movimientos','producto_id') ? 'producto_id' : null;

        $topLabels = collect();
        $topData   = collect();

        if ($col) {
            $desde = Carbon::now()->subDays(30)->startOfDay();

            $top = Movimiento::where('tipo','salida')
                ->where('created_at','>=', $desde)
                ->select($col.' as pid', DB::raw('SUM(cantidad) as total'))
                ->groupBy('pid')->orderByDesc('total')->limit(7)->get();

            $map = Producto::with('unidad')
                ->whereIn('id', $top->pluck('pid'))->get()->keyBy('id');

            $topLabels = $top->map(function ($row) use ($map) {
                $p = $map->get($row->pid);
                return $p
                    ? ($p->nombre . ($p->unidad?->clave ? ' ('.$p->unidad->clave.')' : ''))
                    : ('ID '.$row->pid);
            });

            $topData = $top->pluck('total')->map(fn($v)=>(float)$v);
        }

        // ===== Gasto mensual (entradas) últimos 6 meses =====
        $gastoRows = Movimiento::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
                DB::raw('SUM(COALESCE(costo_total,0)) as total')
            )
            ->where('tipo','entrada')
            ->where('created_at','>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->groupBy('ym')->orderBy('ym')->get();

        $gastoLabels = $gastoRows->pluck('ym');
        $gastoData   = $gastoRows->pluck('total')->map(fn($v)=>(float)$v);

        return view('dashboard.admin', compact('bajo','ultimos','resumen','topLabels','topData','gastoLabels','gastoData'));
    }

    public function empleado()
    {
        $u = Auth::user();

        $ultimos = Movimiento::with(['producto.unidad','usuario'])
            ->where('user_id', $u->id)
            ->orderByDesc('created_at')->take(10)->get();

        return view('dashboard.empleado', compact('ultimos'));
    }
}
