<?php

namespace App\Http\Controllers;

use App\Models\ListaPedido;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListaPedidoController extends Controller
{
    // GET /listas  → listado de listas
    public function index()
    {
        // Trae las listas con su creador y contadores
        $listas = ListaPedido::with('creador')
            ->latest()
            ->get();

        return view('listas.index', compact('listas'));
    }

    // GET /listas/create → formulario de nueva lista (borrador)
    public function create()
    {
        return view('listas.create');
    }

    // POST /listas → crear lista (status=borrador)
    public function store(Request $request)
    {
        $request->validate([
            'comentario' => 'nullable|string|max:255',
        ]);

        $lista = ListaPedido::create([
            'user_id'   => Auth::id(),
            'status'    => 'borrador',
            'comentario'=> $request->comentario,
        ]);

        return redirect()->route('listas.show', $lista->id)
            ->with('success', 'Lista creada. Agrega los materiales que necesitas.');
    }

    // GET /listas/{lista} → ver/gestionar items de la lista
    public function show(ListaPedido $lista)
    {
        // Para el formulario de agregar items
        $productos  = Producto::orderBy('nombre')->get();
        $proveedors = Proveedor::orderBy('nombre')->get();

        // Cargar items con relaciones
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
