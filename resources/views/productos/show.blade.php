@extends('layouts.app')

@section('title','Detalle de producto')

@section('content')
<style>
  :root{--cafe:#8b5e3c;--borde:#d9c9b3}
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px;max-width:720px}
  .kv{display:grid;grid-template-columns:220px 1fr;gap:10px}
  .kv div{padding:8px 10px;border-bottom:1px dashed #eee}
  .key{font-weight:800;color:#5c3a21}
  .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none}
  .btn-primary{background:#8b5e3c;color:#fff}.btn-primary:hover{background:#70472e}
  .btn-back{background:#fff;border:1px solid #d9c9b3;color:#70472e}.btn-back:hover{background:#f2e8db}
  .btn-danger{background:#e74c3c;color:#fff}.btn-danger:hover{background:#c0392b}
  @media(max-width:700px){ .kv{grid-template-columns:1fr} }
</style>

<h1 class="page">Detalles del producto</h1>

<div class="card">
  <div class="kv">
    <div class="key">Código</div>          <div>{{ $producto->codigo }}</div>
    <div class="key">Nombre</div>          <div>{{ $producto->nombre }}</div>
    <div class="key">Unidad</div>          <div>{{ $producto->unidad->descripcion ?? '—' }}</div>
    <div class="key">Categoría</div>       <div>{{ $producto->categoria->nombre ?? '—' }}</div>
    <div class="key">Existencias</div>     <div>{{ $producto->existencias }}</div>
    <div class="key">Stock mínimo</div>    <div>{{ $producto->stock_minimo }}</div>
    <div class="key">Costo promedio</div>  <div>${{ number_format($producto->costo_promedio, 2) }}</div>
    <div class="key">Actualizado</div>     <div>{{ $producto->updated_at->format('d/m/Y H:i') }}</div>
  </div>

  <div class="actions">
    <a class="btn btn-primary" href="{{ route('productos.edit', $producto->id) }}"> Editar</a>
    <a class="btn btn-primary" href="{{ route('kardex.producto', $producto->id) }}"> Ver Kardex</a>
    <a class="btn btn-back" href="{{ route('productos.index') }}">Volver</a>

    <form action="{{ route('productos.destroy', $producto->id) }}" method="POST" onsubmit="return confirm('¿Eliminar {{ $producto->nombre }}?')">
      @csrf @method('DELETE')
      <button class="btn btn-danger" type="submit">Eliminar</button>
    </form>
  </div>
</div>
@endsection
