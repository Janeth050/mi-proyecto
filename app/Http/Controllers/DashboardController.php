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
        // === Existencias bajas (<=) ===
        $bajo = Producto::whereColumn('existencias', '<=', 'stock_minimo')
            ->with('unidad')
            ->orderBy('nombre')
            ->take(8)
            ->get();

        // === Últimos 5 movimientos ===
        $ultimos = Movimiento::with(['producto.unidad','usuario'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // === KPIs "hoy" respetando timezone de la app ===
        $hoyInicio = Carbon::now(config('app.timezone'))->startOfDay()->timezone('UTC');
        $hoyFin    = Carbon::now(config('app.timezone'))->endOfDay()->timezone('UTC');

        $resumen = [
            'productos_total' => Producto::count(),
            'bajo_stock'      => Producto::whereColumn('existencias','<=','stock_minimo')->count(),
            'entradas_hoy'    => Movimiento::where('tipo','entrada')
                                  ->whereBetween('created_at', [$hoyInicio, $hoyFin])->count(),
            'salidas_hoy'     => Movimiento::where('tipo','salida')
                                  ->whereBetween('created_at', [$hoyInicio, $hoyFin])->count(),
        ];

        // ===== TOP usados 30 días (salidas) =====
        $col = Schema::hasColumn('movimientos','producto_id') ? 'producto_id' : null;

        $topLabels = collect();
        $topData   = collect();

        if ($col) {
            $desdeLocal = Carbon::now(config('app.timezone'))->subDays(30)->startOfDay();
            $desdeUTC   = $desdeLocal->clone()->timezone('UTC');

            $top = Movimiento::where('tipo','salida')
                ->where('created_at','>=', $desdeUTC)
                ->select($col.' as pid', DB::raw('SUM(cantidad) as total'))
                ->groupBy('pid')->orderByDesc('total')->limit(7)->get();

            $map = \App\Models\Producto::with('unidad')
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
        $hace6Local = Carbon::now(config('app.timezone'))->subMonths(6)->startOfMonth();
        $hace6UTC   = $hace6Local->clone()->timezone('UTC');

        $gastoRows = Movimiento::select(
                DB::raw("DATE_FORMAT(CONVERT_TZ(created_at,'+00:00','+00:00'), '%Y-%m') as ym"),
                DB::raw('SUM(COALESCE(costo_total,0)) as total')
            )
            ->where('tipo','entrada')
            ->where('created_at','>=', $hace6UTC)
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
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('dashboard.empleado', compact('ultimos'));
    }
}
