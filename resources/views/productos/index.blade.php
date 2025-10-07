@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Lista de Productos</title>
  <style>
    body { font-family: Arial; background-color: #f9f3e9; color: #5c3a21; }
    table { width: 90%; margin: 20px auto; border-collapse: collapse; box-shadow: 0 4px 12px rgba(0,0,0,.1);}
    th, td { padding: 10px; border: 1px solid #d9c9b3; text-align: center; }
    th { background-color: #8b5e3c; color: white; }
    a.boton { background: #8b5e3c; color: white; padding: 6px 10px; border-radius: 5px; text-decoration: none;}
    a.boton:hover { background: #70472e; }
  </style>
</head>
<body>
  <h1 style="text-align:center;">Inventario de Productos</h1>

  <div style="text-align:center; margin-bottom:15px;">
    <a href="{{ route('productos.create') }}" class="boton">+ Agregar Producto</a>
  </div>

  <table>
    <thead>
      <tr>
        <th>Código</th>
        <th>Nombre</th>
        <th>Unidad</th>
        <th>Categoría</th>
        <th>Existencias</th>
        <th>Stock mínimo</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($productos as $producto)
        <tr>
          <td>{{ $producto->codigo }}</td>
          <td>{{ $producto->nombre }}</td>
          <td>{{ $producto->unidad->descripcion ?? '-' }}</td>
          <td>{{ $producto->categoria->nombre ?? '-' }}</td>
          <td>{{ $producto->existencias }}</td>
          <td>{{ $producto->stock_minimo }}</td>
          <td>
            <a href="{{ route('productos.edit', $producto->id) }}" class="boton">Editar</a>
            <a href="{{ route('kardex.producto', $producto->id) }}" class="boton" style="background:#6f5331;">Kardex </a>

            <form action="{{ route('productos.destroy', $producto->id) }}" method="POST" style="display:inline;">
              @csrf
              @method('DELETE')
              <button type="submit" class="boton" style="background:#c0392b;">Eliminar</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
@endsection