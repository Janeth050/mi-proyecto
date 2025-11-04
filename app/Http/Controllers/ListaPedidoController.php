<?php

namespace App\Http\Controllers;

use App\Models\ListaPedido;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListaPedidoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // 🔒 Admin obligatorio para todo el controlador
        $this->middleware(function ($request, $next) {
            $u = $request->user();
            if (!$u) abort(401, 'Debes iniciar sesión.');
            $role = strtolower((string)($u->role ?? $u->rol ?? ''));
            if ($role !== 'admin') abort(403, 'Solo administradores pueden acceder a Listas.');
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $status = $request->get('status');
        $q      = $request->get('q');

        $listas = ListaPedido::with('creador')
            ->withCount('items')
            ->when($status, fn($qL)=> $qL->where('status', $status))
            ->when($q, function($qL) use ($q){
                $qL->where(function($sub) use ($q){
                    $sub->where('comentario','like',"%{$q}%")
                        ->orWhereHas('creador', fn($u)=> $u->where('name','like',"%{$q}%"));
                });
            })
            ->latest()
            ->get();

        $productos  = Producto::orderBy('nombre')->get(['id','nombre']);
        $proveedors = Proveedor::orderBy('nombre')->get(['id','nombre']);

        return view('listas.index', compact('listas','status','q','productos','proveedors'));
    }

    /** Crear lista (JSON, status=borrador) */
    public function store(Request $request)
    {
        $request->validate([
            'comentario' => 'nullable|string|max:255',
            'productos'  => 'nullable|array',
        ]);

        $lista = ListaPedido::create([
            'user_id'    => Auth::id(),
            'status'     => 'borrador',
            'comentario' => $request->comentario,
        ]);

        if ($request->filled('productos')) {
            foreach ($request->productos as $productoId => $cant) {
                $cantidad = (int) $cant;
                if ($cantidad > 0) {
                    $lista->items()->create([
                        'producto_id'     => $productoId,
                        'cantidad'        => $cantidad,
                        'proveedor_id'    => null,
                        'precio_estimado' => null,
                    ]);
                }
            }
        }

        return response()->json([
            'ok'    => true,
            'lista' => $lista->loadCount('items'),
            'message' => 'Lista creada. Agrega los materiales que necesitas.',
        ]);
    }

    /** Detalle (JSON) para modal */
    public function show(ListaPedido $lista)
    {
        $lista->load(['items.producto.unidad', 'items.proveedor', 'creador']);
        return response()->json([
            'ok'    => true,
            'lista' => $lista,
            'total_estimado' => $lista->total_estimado,
        ]);
    }

    public function enviar(ListaPedido $lista)
    {
        if ($lista->status !== 'borrador') {
            return response()->json(['ok'=>false,'message'=>'Solo las listas en borrador pueden enviarse.'], 422);
        }
        if ($lista->items()->count() === 0) {
            return response()->json(['ok'=>false,'message'=>'No puedes enviar una lista vacía.'], 422);
        }

        $lista->update(['status' => 'enviada']);
        return response()->json(['ok'=>true,'message'=>'Lista marcada como ENVIADA.','lista'=>$lista]);
    }

    public function cerrar(ListaPedido $lista)
    {
        if (!in_array($lista->status, ['enviada'])) {
            return response()->json(['ok'=>false,'message'=>'Solo una lista ENVIADA puede cerrarse.'], 422);
        }
        $lista->update(['status' => 'cerrada']);
        return response()->json(['ok'=>true,'message'=>'Lista marcada como CERRADA.','lista'=>$lista]);
    }

    public function cancelar(ListaPedido $lista)
    {
        if ($lista->status === 'cerrada') {
            return response()->json(['ok'=>false,'message'=>'No puedes cancelar una lista cerrada.'], 422);
        }
        $lista->update(['status' => 'cancelada']);
        return response()->json(['ok'=>true,'message'=>'Lista marcada como CANCELADA.','lista'=>$lista]);
    }

    /** Eliminar borrador */
    public function destroy(ListaPedido $lista)
    {
        if ($lista->status !== 'borrador') {
            return response()->json(['ok'=>false,'message'=>'Solo puedes eliminar listas en borrador.'], 422);
        }
        $lista->items()->delete();
        $lista->delete();

        return response()->json(['ok'=>true,'message'=>'Lista eliminada.']);
    }

    public function quickAddFromProducto(Request $request, Producto $producto)
    {
    $user = $request->user();

    $data = $request->validate([
        'cantidad'        => ['required','integer','min:1'],
        'proveedor_id'    => ['nullable','exists:proveedors,id'],
        'precio_estimado' => ['nullable','numeric','min:0'],
    ]);

    // ✅ SIEMPRE crear una nueva lista en borrador del usuario actual
    $lista = ListaPedido::create([
        'user_id'    => $user->id, // <-- CORREGIDO: columna real de tu migración
        'status'     => 'borrador',
        'comentario' => 'Creada automáticamente desde productos',
    ]);

    // Agregar el ítem a esa nueva lista
    $item = $lista->items()->create([
        'producto_id'     => $producto->id,
        'cantidad'        => $data['cantidad'],
        'proveedor_id'    => $data['proveedor_id'] ?? null,
        'precio_estimado' => $data['precio_estimado'] ?? null,
    ]);

    return response()->json([
        'ok'        => true,
        'message'   => 'Producto agregado a nueva lista.',
        'lista_id'  => $lista->id,
        'item_id'   => $item->id,
    ]);
}


}
