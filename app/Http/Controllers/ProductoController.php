<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

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
            ->orderByDesc('id')
            ->get();

        $categorias = Categoria::orderBy('nombre')->get();
        $unidades   = Unidad::orderBy('descripcion')->get();

        return view('productos.index', compact('productos','categorias','unidades','q'));
    }

    public function show(Producto $producto)
    {
        $producto->load(['categoria','unidad']);
        $enAlerta = (int)$producto->existencias < (int)$producto->stock_minimo;

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

        $cat = Categoria::create($data);
        return response()->json(['ok'=>true,'message'=>'Categoría creada.','categoria'=>$cat]);
    }

    // Eliminar categoría (con control FK)
    public function destroyCategoriaInline(Categoria $categoria)
    {
        $this->authorizeAdmin();

        try {
            $categoria->delete();
            return response()->json(['ok'=>true,'message'=>'Categoría eliminada.']);
        } catch (QueryException $e) {
            // 23000 = violación de restricción (FK en uso)
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

        // Generar clave única basada en la descripción (máx 20)
        $base  = Str::slug($data['descripcion'], '_');
        $clave = substr($base, 0, 20) ?: substr(Str::random(8), 0, 8);
        $orig  = $clave; $i = 1;

        while (Unidad::where('clave', $clave)->exists()) {
            $suf   = '_'.$i++;
            $clave = substr($orig, 0, max(1, 20 - strlen($suf))) . $suf;
        }

        $uni = Unidad::create([
            'clave'       => $clave,
            'descripcion' => $data['descripcion'],
        ]);

        return response()->json(['ok'=>true,'message'=>'Unidad creada.','unidad'=>$uni]);
    }

    // Eliminar unidad (bloquea si está en uso)
    public function destroyUnidadInline(Unidad $unidad)
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

    protected function checkAndNotifyLowStock(Producto $producto): void
    {
        try {
            $bajo = (int)$producto->existencias < (int)$producto->stock_minimo;
            if (!$bajo) return;

            $adminPhone = env('ADMIN_PHONE');
            if (!$adminPhone) return;

            $msg = sprintf(
                "⚠️ Bajo stock: %s (%s). Existencias: %d | Mínimo: %d",
                $producto->nombre, $producto->codigo,
                (int)$producto->existencias, (int)$producto->stock_minimo
            );

            $waUrl   = env('NOTIF_WHATSAPP_URL');
            $waToken = env('NOTIF_WHATSAPP_TOKEN');
            if ($waUrl && $waToken) { Http::withToken($waToken)->post($waUrl, ['to'=>$adminPhone,'message'=>$msg]); return; }

            $smsUrl   = env('NOTIF_SMS_URL');
            $smsToken = env('NOTIF_SMS_TOKEN');
            if ($smsUrl && $smsToken) { Http::withToken($smsToken)->post($smsUrl, ['to'=>$adminPhone,'message'=>$msg]); return; }

            Log::info('[LOW_STOCK] '.$msg);
        } catch (\Throwable $e) {
            Log::error('Error enviando notificación de bajo stock: '.$e->getMessage());
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
