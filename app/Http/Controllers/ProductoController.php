<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Unidad;
use App\Models\ListaPedido;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $q   = $request->get('q');
        $low = $request->boolean('low');

        $productos = Producto::with(['unidad','categoria'])
            ->when($q, function($query) use ($q){
                $query->where(function($qq) use ($q){
                    $qq->where('codigo','like',"%$q%")
                       ->orWhere('nombre','like',"%$q%");
                })
                ->orWhereHas('categoria', function($qc) use ($q){
                    $qc->where('nombre','like',"%$q%");
                })
                ->orWhereHas('unidad', function($qu) use ($q){
                    $qu->where('descripcion','like',"%$q%");
                });
            })
            ->when($low, fn($qq)=> $qq->bajoStock())
            ->orderByDesc('id')
            ->get();

        // IDs ya en la lista en borrador del usuario, para ocultar el botón "Agregar a lista"
        $idsEnLista = [];
        if (Auth::check()) {
            $listaBorrador = ListaPedido::where('user_id', Auth::id())
                ->where('status', 'borrador')
                ->first();
            if ($listaBorrador) {
                $idsEnLista = $listaBorrador->items()->pluck('producto_id')->all();
            }
        }

        $categorias = Categoria::orderBy('nombre')->get();
        $unidades   = Unidad::orderBy('descripcion')->get();

        return view('productos.index', compact('productos','categorias','unidades','q','low','idsEnLista'));
    }

    public function show(Producto $producto)
    {
        $producto->load(['categoria','unidad']);
        // <= para que 5 de 5 sea alerta
        $enAlerta = (int)$producto->existencias <= (int)$producto->stock_minimo;

        return response()->json([
            'ok'        => true,
            'producto'  => $producto,
            'en_alerta' => $enAlerta,
            'sugerencia_reposicion' => max(0, (int)$producto->stock_minimo - (int)$producto->existencias),
        ]);
    }

    public function edit(Producto $producto)
    {
        $producto->load(['categoria','unidad']);
        return response()->json(['ok'=>true,'producto'=>$producto]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $request->merge([
            'categoria_id' => $request->filled('categoria_id') ? (int)$request->categoria_id : null,
            'unidad_id'    => $request->filled('unidad_id')    ? (int)$request->unidad_id    : null,
            'existencias'  => (int) $request->existencias,
            'stock_minimo' => (int) $request->stock_minimo,
        ]);

        $data = $request->validate([
            'codigo'               => ['required','string','max:64','unique:productos,codigo'],
            'nombre'               => ['required','string','max:255'],
            'unidad_id'            => ['required','integer','exists:unidades,id'],
            'categoria_id'         => ['nullable','integer','exists:categorias,id'],
            'existencias'          => ['required','integer','min:0'],
            'stock_minimo'         => ['required','integer','min:0'],
            'costo_promedio'       => ['nullable','numeric','min:0'],
            'presentacion_detalle' => ['nullable','string','max:255'],
        ]);

        $payload = $this->filtrarCamposSegunTabla($data);

        $producto = Producto::create($payload)->load(['categoria','unidad']);

        $this->checkAndNotifyLowStock($producto);

        return response()->json(['ok'=>true,'producto'=>$producto]);
    }

    public function update(Request $request, Producto $producto)
    {
        $this->authorizeAdmin();

        $request->merge([
            'categoria_id' => $request->filled('categoria_id') ? (int)$request->categoria_id : null,
            'unidad_id'    => $request->filled('unidad_id')    ? (int)$request->unidad_id    : null,
            'existencias'  => (int) $request->existencias,
            'stock_minimo' => (int) $request->stock_minimo,
        ]);

        $data = $request->validate([
            'codigo'               => ['required','string','max:64', Rule::unique('productos','codigo')->ignore($producto->id)],
            'nombre'               => ['required','string','max:255'],
            'unidad_id'            => ['required','integer','exists:unidades,id'],
            'categoria_id'         => ['nullable','integer','exists:categorias,id'],
            'existencias'          => ['required','integer','min:0'],
            'stock_minimo'         => ['required','integer','min:0'],
            'costo_promedio'       => ['nullable','numeric','min:0'],
            'presentacion_detalle' => ['nullable','string','max:255'],
        ]);

        $payload = $this->filtrarCamposSegunTabla($data);

        $producto->update($payload);
        $producto->load(['categoria','unidad']);

        $this->checkAndNotifyLowStock($producto);

        return response()->json(['ok'=>true,'producto'=>$producto]);
    }

    public function destroy(Producto $producto)
    {
        $this->authorizeAdmin();
        $producto->delete();
        return response()->json(['ok'=>true,'message'=>'Producto eliminado correctamente.']);
    }

    // --------- Inline categoría
    public function storeCategoriaInline(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'nombre' => ['required','string','max:100','unique:categorias,nombre'],
        ]);

        $cat = \App\Models\Categoria::create($data);
        return response()->json(['ok'=>true,'message'=>'Categoría creada.','categoria'=>$cat]);
    }

    public function destroyCategoriaInline(\App\Models\Categoria $categoria)
    {
        $this->authorizeAdmin();

        try {
            $categoria->delete();
            return response()->json(['ok'=>true,'message'=>'Categoría eliminada.']);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se puede eliminar: la categoría está en uso por uno o más productos.'
                ], 409);
            }
            return response()->json(['ok'=>false,'message'=>'Error al eliminar categoría.'], 500);
        }
    }

    // --------- Inline unidad
    public function storeUnidadInline(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'descripcion' => ['required','string','max:100','unique:unidades,descripcion'],
        ]);

        $base  = Str::slug($data['descripcion'], '_');
        $clave = substr($base, 0, 20) ?: substr(Str::random(8), 0, 8);
        $orig  = $clave; $i = 1;

        while (\App\Models\Unidad::where('clave', $clave)->exists()) {
            $suf   = '_'.$i++;
            $clave = substr($orig, 0, max(1, 20 - strlen($suf))) . $suf;
        }

        $uni = \App\Models\Unidad::create([
            'clave'       => $clave,
            'descripcion' => $data['descripcion'],
        ]);

        return response()->json(['ok'=>true,'message'=>'Unidad creada.','unidad'=>$uni]);
    }

    public function destroyUnidadInline(\App\Models\Unidad $unidad)
    {
        $this->authorizeAdmin();

        $enUso = Producto::where('unidad_id', $unidad->id)->exists();
        if ($enUso) {
            return response()->json([
                'ok' => false,
                'message' => 'No se puede eliminar: hay productos que usan esta unidad.'
            ], 422);
        }

        $unidad->delete();
        return response()->json(['ok'=>true,'message'=>'Unidad eliminada.']);
    }

    public function kardexJson(Producto $producto)
    {
        // Mantengo stub si aún no lo ocupas.
        $movs = [];
        return response()->json([
            'ok'=>true,
            'producto'=>$producto->only(['id','codigo','nombre']),
            'movs'=>$movs
        ]);
    }

    public function sugerenciaReposicion(Producto $producto)
    {
        $sugerida = max(0, (int)$producto->stock_minimo - (int)$producto->existencias);
        return response()->json([
            'ok'=>true,
            'producto'=>$producto->only(['id','codigo','nombre']),
            'sugerida'=>$sugerida,
        ]);
    }

    // ====== NOTIFICACIONES ======
    protected function checkAndNotifyLowStock(Producto $producto): void
    {
        try {
            // <= para considerar el umbral exacto
            $bajo = (int)$producto->existencias <= (int)$producto->stock_minimo;
            if (!$bajo) return;

            // Admins con alertas activas y número guardado
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
                Log::info('[LOW_STOCK] No hay administradores con número configurado.');
                return;
            }

            $msg = sprintf(
                "⚠️ *Alerta de bajo stock* ⚠️\n\nProducto: %s (%s)\nExistencias: %d\nStock mínimo: %d\n\n📦 Reponer pronto.",
                $producto->nombre,
                $producto->codigo,
                (int)$producto->existencias,
                (int)$producto->stock_minimo
            );

            $ok = $this->sendCallMeBot($admins, $msg);

            if ($ok) {
                Log::info('[LOW_STOCK] Enviado correctamente a: ' . implode(', ', $admins));
            } else {
                Log::warning('[LOW_STOCK] No se pudo enviar el mensaje de WhatsApp.');
            }

        } catch (\Throwable $e) {
            Log::error('Error al enviar notificación WhatsApp: ' . $e->getMessage());
        }
    }

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
                    'phone'  => $phone,   // 52XXXXXXXXXX
                    'text'   => $message,
                    'apikey' => $apiKey,
                ]);

                $url = "https://api.callmebot.com/whatsapp.php?" . $query;

                $resp = Http::withOptions([
                    'verify'  => false,   // si tu hosting da problema SSL, esto lo evita
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

    protected function authorizeAdmin()
    {
        $u = Auth::user();
        if (!$u) abort(401, 'Debes iniciar sesión.');

        if (isset($u->is_admin) && (bool)$u->is_admin === true) return;
        $role = strtolower((string)($u->role ?? $u->rol ?? ''));
        if (in_array($role, ['admin','administrador','administradora','superadmin','super administrador','adm'], true)) return;
        if (app()->bound('gate') && app('gate')->allows('manage-products')) return;

        abort(403, 'No autorizado (solo administradores).');
    }

    private function filtrarCamposSegunTabla(array $data): array
    {
        $cols = Schema::getColumnListing('productos');
        return array_intersect_key($data, array_flip($cols));
    }
}
