<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Unidad;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /** Muestra la lista de productos.*/
    public function index()
    {
        // Trae todos los productos junto con sus relaciones (unidad y categoría)
        $productos = Producto::with(['unidad', 'categoria'])->get();

        // Retorna la vista 'productos.index' y le envía los productos
        return view('productos.index', compact('productos'));
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     */
    public function create()
    {
        // Carga todas las categorías y unidades disponibles para el formulario
        $categorias = Categoria::all();
        $unidades = Unidad::all();

        // Retorna la vista 'productos.create' con los datos
        return view('productos.create', compact('categorias', 'unidades'));
    }

    /**
     * Guarda un nuevo producto en la base de datos.
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'codigo' => 'required|unique:productos,codigo|max:64',
            'nombre' => 'required|max:255',
            'unidad_id' => 'required|exists:unidades,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'existencias' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'costo_promedio' => 'nullable|numeric|min:0',
        ]);

        // Crea el nuevo producto con los datos validados
        Producto::create($request->all());

        // Redirige a la lista de productos con mensaje de éxito
        return redirect()->route('productos.index')->with('success', 'Producto agregado correctamente.');
    }

    /**
     * Muestra los detalles de un producto específico.
     */
    public function show(Producto $producto)
    {
        return view('productos.show', compact('producto'));
    }

    /**
     * Muestra el formulario para editar un producto existente.
     */
    public function edit(Producto $producto)
    {
        // Trae las categorías y unidades para el formulario
        $categorias = Categoria::all();
        $unidades = Unidad::all();

        return view('productos.edit', compact('producto', 'categorias', 'unidades'));
    }

    /**
     * Actualiza los datos de un producto.
     */
    public function update(Request $request, Producto $producto)
    {
        // Validación de los datos
        $request->validate([
            'nombre' => 'required|max:255',
            'unidad_id' => 'required|exists:unidades,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'existencias' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'costo_promedio' => 'nullable|numeric|min:0',
        ]);

        // Actualiza los datos del producto
        $producto->update($request->all());

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Elimina (soft delete) un producto del inventario.
     */
    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente.');
    }

    
}
