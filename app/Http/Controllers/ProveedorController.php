<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    /**
     * Mostrar todos los proveedores
     */
    public function index()
    {
        // Trae todos los proveedores (sin incluir los eliminados)
        $proveedors = Proveedor::all();
        return view('proveedors.index', compact('proveedors'));
    }

    /**
     * Mostrar formulario para crear un nuevo proveedor
     */
    public function create()
    {
        // Solo devuelve la vista del formulario
        return view('proveedors.create');
    }

    /**
     * Guardar el nuevo proveedor en la base de datos
     */
    public function store(Request $request)
    {
        // Validación de los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255|unique:proveedors,nombre',
            'telefono' => 'nullable|string|max:50',
            'correo' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
        ]);

        // Crea el registro en la base de datos
        Proveedor::create($request->all());

        // Redirige a la lista con mensaje de éxito
        return redirect()->route('proveedors.index')->with('success', 'Proveedor agregado correctamente.');
    }

    /**
     * Mostrar los detalles de un proveedor
     */
    public function show(Proveedor $proveedor)
    {
        return view('proveedors.show', compact('proveedor'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Proveedor $proveedor)
    {
        return view('proveedors.edit', compact('proveedor'));
    }

    /**
     * Actualizar datos del proveedor
     */
    public function update(Request $request, Proveedor $proveedor)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:proveedors,nombre,' . $proveedor->id,
            'telefono' => 'nullable|string|max:50',
            'correo' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
        ]);

        $proveedor->update($request->all());

        return redirect()->route('proveedors.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    /**
     * Eliminar proveedor (soft delete)
     */
    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();
        return redirect()->route('proveedors.index')->with('success', 'Proveedor eliminado correctamente.');
    }
}
