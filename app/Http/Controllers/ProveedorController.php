<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    // LISTA: muestra todos los proveedores
    public function index()
    {
        // Paginamos para no traer todo de golpe
        $proveedores = Proveedor::orderBy('nombre')->paginate(10);
        return view('proveedores.index', compact('proveedores'));
    }

    // FORMULARIO: crear nuevo proveedor
    public function create()
    {
        return view('proveedores.create');
    }

    // GUARDAR: recibe el POST del formulario de create
    public function store(Request $request)
    {
        // Validación mínima (explica al usuario si falta algo)
        $data = $request->validate([
            'nombre'    => 'required|string|max:255',
            'telefono'  => 'nullable|string|max:30',
            'correo'    => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'notas'     => 'nullable|string',
        ]);

        Proveedor::create($data);
        return redirect()->route('proveedores.index')->with('ok', 'Proveedor creado correctamente.');
    }

    // FORMULARIO: editar existente
    public function edit(Proveedor $proveedore)
    {
        // Tip: el nombre del parámetro es $proveedore porque Laravel pluraliza raro `proveedors` -> {proveedore}
        return view('proveedores.edit', ['proveedor' => $proveedore]);
    }

    // ACTUALIZAR: recibe el POST del formulario de edit
    public function update(Request $request, Proveedor $proveedore)
    {
        $data = $request->validate([
            'nombre'    => 'required|string|max:255',
            'telefono'  => 'nullable|string|max:30',
            'correo'    => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'notas'     => 'nullable|string',
        ]);

        $proveedore->update($data);
        return redirect()->route('proveedores.index')->with('ok', 'Proveedor actualizado.');
    }

    // ELIMINAR: borra un proveedor
    public function destroy(Proveedor $proveedore)
    {
        $proveedore->delete();
        return redirect()->route('proveedores.index')->with('ok', 'Proveedor eliminado.');
    }

    // (Opcional) VER DETALLE
    public function show(Proveedor $proveedore)
    {
        return view('proveedores.show', ['proveedor' => $proveedore]);
    }
}
