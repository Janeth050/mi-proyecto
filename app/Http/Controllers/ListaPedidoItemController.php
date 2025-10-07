<?php

namespace App\Http\Controllers;

use App\Models\ListaPedido;
use App\Models\ListaPedidoItem;
use Illuminate\Http\Request;

class ListaPedidoItemController extends Controller
{
    // POST /listas/{lista}/items → agrega ítem (solo en borrador)
    public function store(Request $request, ListaPedido $lista)
    {
        if ($lista->status !== 'borrador') {
            return back()->with('error', 'Solo puedes modificar una lista en borrador.');
        }

        $request->validate([
            'producto_id'     => 'required|exists:productos,id',
            'cantidad'        => 'required|integer|min:1',
            'proveedor_id'    => 'nullable|exists:proveedors,id',
            'precio_estimado' => 'nullable|numeric|min:0',
        ]);

        // Si el producto ya existe en la lista, acumula cantidad
        $item = $lista->items()
            ->where('producto_id', $request->producto_id)
            ->where('proveedor_id', $request->proveedor_id)
            ->first();

        if ($item) {
            $item->update([
                'cantidad'        => $item->cantidad + (int)$request->cantidad,
                'precio_estimado' => $request->precio_estimado ?? $item->precio_estimado,
            ]);
        } else {
            $lista->items()->create($request->only('producto_id','cantidad','proveedor_id','precio_estimado'));
        }

        return back()->with('success', 'Ítem agregado a la lista.');
    }

    // PUT /listas/{lista}/items/{item} → actualizar (solo en borrador)
    public function update(Request $request, ListaPedido $lista, ListaPedidoItem $item)
    {
        if ($lista->status !== 'borrador') {
            return back()->with('error', 'Solo puedes modificar una lista en borrador.');
        }
        if ($item->lista_pedido_id !== $lista->id) {
            abort(404);
        }

        $request->validate([
            'cantidad'        => 'required|integer|min:1',
            'precio_estimado' => 'nullable|numeric|min:0',
            'proveedor_id'    => 'nullable|exists:proveedors,id',
        ]);

        $item->update($request->only('cantidad','precio_estimado','proveedor_id'));

        return back()->with('success', 'Ítem actualizado.');
    }

    // DELETE /listas/{lista}/items/{item} → eliminar (solo en borrador)
    public function destroy(ListaPedido $lista, ListaPedidoItem $item)
    {
        if ($lista->status !== 'borrador') {
            return back()->with('error', 'Solo puedes modificar una lista en borrador.');
        }
        if ($item->lista_pedido_id !== $lista->id) {
            abort(404);
        }

        $item->delete();
        return back()->with('success', 'Ítem eliminado.');
    }
}
