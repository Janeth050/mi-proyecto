@extends('layouts.app')

@section('title','Editar producto')

@section('content')
<style>
  :root{--cafe:#8b5e3c;--hover:#70472e;--borde:#d9c9b3}
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px;max-width:760px}
  .form{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .form .full{grid-column:1 / -1}
  label{font-weight:700;color:#5c3a21}
  input,select{width:100%;padding:10px;border:1px solid var(--borde);border-radius:10px}
  .help{color:#7a6b5f;font-size:12px}
  .errors{color:#c0392b;font-size:13px;margin-top:4px}
  .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-back{background:#fff;border:1px solid var(--borde);color:#70472e}.btn-back:hover{background:#f2e8db}
  @media(max-width:700px){ .form{grid-template-columns:1fr} }
</style>

<h1 class="page">Editar producto</h1>

<div class="card">
  <form class="form" action="{{ route('productos.update', $producto->id) }}" method="POST">
    @csrf @method('PUT')

    <div class="full">
      <label for="codigo">Código</label>
      <input type="text" id="codigo" name="codigo" value="{{ old('codigo',$producto->codigo) }}" readonly>
      <div class="help">* El código no se puede modificar.</div>
    </div>

    <div class="full">
      <label for="nombre">Nombre del producto</label>
      <input type="text" id="nombre" name="nombre" value="{{ old('nombre',$producto->nombre) }}" required>
      @error('nombre') <div class="errors">{{ $message }}</div> @enderror
    </div>

    <div>
      <label for="unidad_id">Unidad de medida</label>
      <select id="unidad_id" name="unidad_id" required>
        @foreach($unidades as $u)
          <option value="{{ $u->id }}" {{ old('unidad_id',$producto->unidad_id)==$u->id?'selected':'' }}>
            {{ $u->descripcion }}
          </option>
        @endforeach
      </select>
      @error('unidad_id') <div class="errors">{{ $message }}</div> @enderror
    </div>

    <div>
      <label for="categoria_id">Categoría</label>
      <select id="categoria_id" name="categoria_id">
        <option value="">— Sin categoría —</option>
        @foreach($categorias as $c)
          <option value="{{ $c->id }}" {{ old('categoria_id',$producto->categoria_id)==$c->id?'selected':'' }}>
            {{ $c->nombre }}
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label for="existencias">Existencias</label>
      <input type="number" id="existencias" name="existencias" value="{{ old('existencias',$producto->existencias) }}" min="0" required>
    </div>

    <div>
      <label for="stock_minimo">Stock mínimo</label>
      <input type="number" id="stock_minimo" name="stock_minimo" value="{{ old('stock_minimo',$producto->stock_minimo) }}" min="0" required>
    </div>

    <div class="full">
      <label for="costo_promedio">Costo promedio</label>
      <input type="number" id="costo_promedio" name="costo_promedio" value="{{ old('costo_promedio',$producto->costo_promedio) }}" min="0" step="0.0001">
    </div>

    <div class="actions full">
      <button type="submit" class="btn btn-primary"> Actualizar</button>
      <a class="btn btn-back" href="{{ route('productos.index') }}">Volver</a>
    </div>
  </form>
</div>
@endsection
