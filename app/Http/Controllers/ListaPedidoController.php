<?php

namespace App\Http\Controllers;

use App\Models\ListaPedido;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListaPedidoController extends Controller
{
    // GET /listas  → listado con #items y total estimado
    public function index()
    {
        // Carga creador y un contador de items para tabla
        $listas = ListaPedido::with('creador')
            ->withCount('items')
            ->latest()
            ->get();

        return view('listas.index', compact('listas'));
    }

    // GET /listas/create → formulario nueva lista (borrador)
    public function create()
    {
        // (Opcional) productos con bajo stock por si quieres mostrarlos aquí
        $bajoStock  = Producto::with('unidad')
                        ->whereColumn('existencias','<','stock_minimo')
                        ->orderBy('nombre')->get();

        // (Opcional) para sugerir proveedor en create
        $proveedors = Proveedor::orderBy('nombre')->get();

        // Si tu vista create.blade.php sólo tiene “comentario”, no pasa nada.
        // Estos compact son inocuos si no los usas en la vista.
        return view('listas.create', compact('bajoStock','proveedors'));
    }

    // POST /listas → crear lista (status=borrador)
    public function store(Request $request)
    {
        $request->validate([
            'comentario' => 'nullable|string|max:255',
            // Si en create mandas productos[ID]=cantidad, esto los capta:
            'productos'  => 'nullable|array',
        ]);

        $lista = ListaPedido::create([
            'user_id'    => Auth::id(),
            'status'     => 'borrador',
            'comentario' => $request->comentario,
        ]);

        // Si venían cantidades desde el create, crea ítems de una vez
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

        return redirect()->route('listas.show', $lista->id)
            ->with('success', 'Lista creada. Agrega los materiales que necesitas.');
    }

    // GET /listas/{lista} → ver/gestionar items de la lista
    public function show(ListaPedido $lista)
    {
        // Productos y proveedores para los selects del formulario de ítems
        $productos  = Producto::orderBy('nombre')->get();
        $proveedors = Proveedor::orderBy('nombre')->get();

        // Carga relaciones para la tabla
        $lista->load(['items.producto', 'items.proveedor', 'creador']);

        return view('listas.show', compact('lista', 'productos', 'proveedors'));
    }

    // POST /listas/{lista}/enviar → cambia a “enviada”
    public function enviar(ListaPedido $lista)
    {
        if ($lista->status !== 'borrador') {
            return back()->with('error', 'Solo las listas en borrador pueden enviarse.');
        }
        if ($lista->items()->count() === 0) {
            return back()->with('error', 'No puedes enviar una lista vacía.');
        }

        $lista->update(['status' => 'enviada']);
        return back()->with('success', 'Lista marcada como ENVIADA.');
    }

    // POST /listas/{lista}/cerrar → cambia a “cerrada”
    public function cerrar(ListaPedido $lista)
    {
        if (!in_array($lista->status, ['enviada'])) {
            return back()->with('error', 'Solo una lista ENVIADA puede cerrarse.');
        }
        $lista->update(['status' => 'cerrada']);
        return back()->with('success', 'Lista marcada como CERRADA.');
    }

    // POST /listas/{lista}/cancelar → cambia a “cancelada”
    public function cancelar(ListaPedido $lista)
    {
        if ($lista->status === 'cerrada') {
            return back()->with('error', 'No puedes cancelar una lista cerrada.');
        }
        $lista->update(['status' => 'cancelada']);
        return back()->with('success', 'Lista marcada como CANCELADA.');
    }

    // DELETE /listas/{lista} → solo si está en borrador
    public function destroy(ListaPedido $lista)
    {
        if ($lista->status !== 'borrador') {
            return back()->with('error', 'Solo puedes eliminar listas en borrador.');
        }
        $lista->items()->delete();
        $lista->delete();

        return redirect()->route('listas.index')->with('success', 'Lista eliminada.');
    }
}
