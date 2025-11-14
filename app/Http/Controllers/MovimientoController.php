<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Kardex;
use App\Models\ListaPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon; // <-- añadido

class MovimientoController extends Controller
{
    /** Hora local a usar (APP_TIMEZONE o Monterrey por defecto) */
    protected string $tz;

    public function __construct()
    {
        $this->middleware('auth');
        $this->tz = config('app.timezone', 'America/Monterrey');
    }

    /**
     * Listado con filtros + catálogos para el modal.
     */
    public function index(Request $request)
    {
        $movimientos = Movimiento::with(['producto.unidad','usuario','proveedor'])
            ->when($request->filled('tipo'), fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($qq) use ($request) {
                    $qq->whereHas('producto', fn($p) => $p->where('nombre', 'like', '%'.$request->q.'%'))
                       ->orWhereHas('usuario', fn($u) => $u->where('name', 'like', '%'.$request->q.'%'));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->query());

        $productos  = Producto::orderBy('nombre')->get(['id','codigo','nombre','existencias']);
        $proveedors = Proveedor::orderBy('nombre')->get(['id','nombre']);

        return view('movimientos.index', compact('movimientos','productos','proveedors'));
    }

    /**
     * (No se usa; el formulario vive en modal)
     */
    public function create()
    {
        $productos  = Producto::orderBy('nombre')->get();
        $proveedors = Proveedor::orderBy('nombre')->get();
        return view('movimientos.create', compact('productos','proveedors'));
    }

    /**
     * Guardar (afecta stock + kardex) y notificar si quedó bajo el mínimo.
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
        $existencias = (int) $producto->existencias;

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
            'status'              => 'confirmado',
            'existencias_despues' => $existencias,
        ]); // <- cierra array y la llamada a create, sin llaves extra

        // Actualiza stock
        $producto->update(['existencias' => $existencias]);

        // Si es ENTRADA, quitar el producto de la lista ACTIVA del admin actual
        if ($request->tipo === 'entrada') {
            $listaActiva = ListaPedido::activaDe(Auth::id())->first();
            if ($listaActiva) {
                $listaActiva->items()->where('producto_id', $producto->id)->delete();
            }
        }

        // Kardex con hora local Monterrey
        Kardex::create([
            'producto_id'    => $producto->id,
            'movimiento_id'  => $mov->id,
            'fecha'          => \Illuminate\Support\Carbon::now($this->tz),
            'tipo'           => $mov->tipo,
            'entrada'        => $mov->tipo === 'entrada' ? $cantidad : 0,
            'salida'         => $mov->tipo === 'salida'  ? $cantidad : 0,
            'saldo'          => $existencias,
            'costo_unitario' => $mov->costo_unitario,
            'costo_total'    => $mov->costo_total,
        ]);

        // Notificar si quedó bajo stock
        $this->notifyLowStockIfNeeded($producto);
    });

    return redirect()->route('movimientos.index')->with('success', 'Movimiento registrado correctamente.');
}


    /**
     * Eliminar registro cancelado (limpieza).
     */
    public function destroy(Movimiento $movimiento)
    {
        if ($movimiento->status !== 'cancelado') {
            return back()->with('error', 'Solo puedes eliminar movimientos cancelados (para evitar desbalanceo).');
        }

        DB::transaction(function () use ($movimiento) {
            Kardex::where('movimiento_id', $movimiento->id)->delete();
            $movimiento->delete();
        });

        return back()->with('success', 'Movimiento eliminado.');
    }

    // ===================== NOTIFICACIONES =====================

    /**
     * Si el producto quedó con existencias por debajo del mínimo, avisa por WhatsApp
     * a todos los administradores que tengan número y alertas activas.
     */
    private function notifyLowStockIfNeeded(Producto $producto): void
    {
        try {
            $bajo = (int)$producto->existencias < (int)$producto->stock_minimo;
            if (!$bajo) return;

            $admins = \App\Models\User::query()
                ->where(function ($q) {
                    $q->where('role', 'admin')->orWhere('is_admin', true);
                })
                ->where('notify_low_stock', true)
                ->whereNotNull('whatsapp_phone')
                ->pluck('whatsapp_phone')
                ->unique()
                ->values()
                ->all();

            if (empty($admins)) {
                Log::info('[LOW_STOCK][MOV] No hay administradores con número configurado.');
                return;
            }

            $msg = sprintf(
                "⚠️ *Bajo stock tras movimiento* ⚠️\n\nProducto: %s (%s)\nExistencias: %d\nStock mínimo: %d\n\n📦 Considera reponer.",
                $producto->nombre,
                $producto->codigo,
                (int)$producto->existencias,
                (int)$producto->stock_minimo
            );

            $this->sendCallMeBot($admins, $msg);

        } catch (\Throwable $e) {
            Log::error('Error al enviar notificación (Movimiento): ' . $e->getMessage());
        }
    }

    /**
     * Envío a CallMeBot (usa CALLMEBOT_ENABLED y CALLMEBOT_APIKEY del .env).
     */
    private function sendCallMeBot(array $phones, string $message): bool
    {
        $apiKey  = env('CALLMEBOT_APIKEY');
        $enabled = filter_var(env('CALLMEBOT_ENABLED', false), FILTER_VALIDATE_BOOLEAN);

        if (!$enabled || !$apiKey) {
            Log::info('[CallMeBot] Desactivado o sin API key.');
            return false;
        }

        try {
            foreach ($phones as $phone) {
                $query = http_build_query([
                    'phone'  => $phone,
                    'text'   => $message,
                    'apikey' => $apiKey,
                ]);

                $url = "https://api.callmebot.com/whatsapp.php?" . $query;

                $resp = Http::withOptions([
                    'verify'  => false,   // si tu hosting tiene problema de SSL, esto lo evita
                    'timeout' => 20,
                ])->get($url);

                if (!$resp->ok()) {
                    Log::error('[CallMeBot] Error HTTP ' . $resp->status() . ': ' . $resp->body());
                    return false;
                }
            }
            return true;

        } catch (\Throwable $e) {
            Log::error('[CallMeBot] Excepción: ' . $e->getMessage());
            return false;
        }
    }
}
