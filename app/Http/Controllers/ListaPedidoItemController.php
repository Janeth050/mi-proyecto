<?php

namespace App\Http\Controllers;

use App\Models\ListaPedido;
use App\Models\ListaPedidoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListaPedidoItemController extends Controller
{
    public function __construct()
    {
          $this->middleware('auth');

        // 🔒 Admin obligatorio para manejar ítems
        $this->middleware(function ($request, $next) {
            $u = $request->user();
            if (!$u) abort(401, 'Debes iniciar sesión.');
            $role = strtolower((string)($u->role ?? $u->rol ?? ''));
            if ($role !== 'admin') abort(403, 'Solo administradores pueden gestionar listas.');
            return $next($request);
        });
    }

    /** POST /listas/{lista}/items → agrega ítem (solo en borrador) (JSON) */
    public function store(Request $request, ListaPedido $lista)
    {
        $this->authorizeAdmin();

        if ($lista->status !== 'borrador') {
            return response()->json(['ok'=>false,'message'=>'Solo puedes modificar una lista en borrador.'], 422);
        }

        $data = $request->validate([
            'producto_id'     => 'required|exists:productos,id',
            'cantidad'        => 'required|integer|min:1',
            'proveedor_id'    => 'nullable|exists:proveedors,id',
            'precio_estimado' => 'nullable|numeric|min:0',
        ]);

        $item = $lista->items()
            ->where('producto_id', $data['producto_id'])
            ->where('proveedor_id', $data['proveedor_id'] ?? null)
            ->first();

        if ($item) {
            $item->update([
                'cantidad'        => $item->cantidad + $data['cantidad'],
                'precio_estimado' => $data['precio_estimado'] ?? $item->precio_estimado,
            ]);
        } else {
            $item = $lista->items()->create($data);
        }

        return response()->json([
            'ok'=>true,
            'message'=>'Ítem agregado a la lista.',
            'item'=>$item->fresh(['producto.unidad','proveedor']),
            'items_count'=>$lista->items()->count(),
        ]);
    }

    /** PUT /listas/{lista}/items/{item} → actualizar (solo borrador) (JSON) */
    public function update(Request $request, ListaPedido $lista, ListaPedidoItem $item)
    {
        $this->authorizeAdmin();

        if ($lista->status !== 'borrador') {
            return response()->json(['ok'=>false,'message'=>'Solo puedes modificar una lista en borrador.'], 422);
        }
        if ($item->lista_pedido_id !== $lista->id) {
            abort(404);
        }

        $data = $request->validate([
            'cantidad'        => 'required|integer|min:1',
            'precio_estimado' => 'nullable|numeric|min:0',
            'proveedor_id'    => 'nullable|exists:proveedors,id',
        ]);

        $item->update($data);

        return response()->json([
            'ok'=>true,
            'message'=>'Ítem actualizado.',
            'item'=>$item->fresh(['producto.unidad','proveedor']),
        ]);
    }

    /** DELETE /listas/{lista}/items/{item} → eliminar (solo borrador) (JSON) */
    public function destroy(ListaPedido $lista, ListaPedidoItem $item)
    {
        $this->authorizeAdmin();

        if ($lista->status !== 'borrador') {
            return response()->json(['ok'=>false,'message'=>'Solo puedes modificar una lista en borrador.'], 422);
        }
        if ($item->lista_pedido_id !== $lista->id) {
            abort(404);
        }

        $item->delete();
        return response()->json(['ok'=>true,'message'=>'Ítem eliminado.','items_count'=>$lista->items()->count()]);
    }

    /** Admin-only helper (tolerante) */
    protected function authorizeAdmin()
    {
        $u = Auth::user();
        if (!$u) abort(401, 'Debes iniciar sesión.');

        if (isset($u->is_admin) && (bool)$u->is_admin === true) return;

        $role = strtolower((string)($u->role ?? $u->rol ?? ''));
        if (in_array($role, ['admin','administrador','superadmin','super administrador','administradora','adm'], true)) return;

        if (app()->bound('gate') && app('gate')->allows('manage-lists')) return;

        abort(403, 'Solo administradores pueden gestionar listas.');
    }
}
