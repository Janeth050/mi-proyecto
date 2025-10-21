@extends('layouts.app')

@section('title','Registrar Movimiento')

@section('content')
<style>
  :root{--cafe:#8b5e3c;--hover:#70472e;--borde:#d9c9b3}
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px;max-width:780px}
  .form{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .form .full{grid-column:1 / -1}
  label{font-weight:700;color:#5c3a21}
  select,input,textarea{width:100%;padding:10px;border:1px solid var(--borde);border-radius:10px}
  textarea{min-height:90px;resize:vertical}
  .errors{color:#c0392b;font-size:13px;margin-top:4px}
  .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-back{background:#fff;border:1px solid var(--borde);color:#70472e}.btn-back:hover{background:#f2e8db}
  @media(max-width:700px){ .form{grid-template-columns:1fr} }
</style>

<h1 class="page">Registrar movimiento</h1>

<div class="card">
  <form class="form" action="{{ route('movimientos.store') }}" method="POST">
    @csrf

    <div class="full">
      <label for="producto_id">Producto</label>
      <select name="producto_id" id="producto_id" required>
        <option value="">— Selecciona producto —</option>
        @foreach ($productos as $p)
          <option value="{{ $p->id }}" {{ old('producto_id')==$p->id?'selected':'' }}>
            {{ $p->nombre }} (Stock: {{ $p->existencias }})
          </option>
        @endforeach
      </select>
      @error('producto_id') <div class="errors">{{ $message }}</div> @enderror
    </div>

    <div>
      <label for="tipo">Tipo</label>
      <select name="tipo" id="tipo" required>
        <option value="">— Selecciona —</option>
        <option value="entrada" {{ old('tipo')=='entrada'?'selected':'' }}>Entrada</option>
        <option value="salida"  {{ old('tipo')=='salida'?'selected':''  }}>Salida</option>
      </select>
      @error('tipo') <div class="errors">{{ $message }}</div> @enderror
    </div>

    <div>
      <label for="cantidad">Cantidad</label>
      <input type="number" name="cantidad" id="cantidad" value="{{ old('cantidad') }}" min="1" required>
      @error('cantidad') <div class="errors">{{ $message }}</div> @enderror
    </div>

    <div>
      <label for="proveedor_id">Proveedor (solo entradas)</label>
      <select name="proveedor_id" id="proveedor_id">
        <option value="">— Ninguno —</option>
        @foreach ($proveedors as $prov)
          <option value="{{ $prov->id }}" {{ old('proveedor_id')==$prov->id?'selected':'' }}>
            {{ $prov->nombre }}
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label for="costo_unitario">Costo unitario</label>
      <input type="number" name="costo_unitario" id="costo_unitario" step="0.01" value="{{ old('costo_unitario') }}" min="0">
    </div>

    <div class="full">
      <label for="descripcion">Descripción / Motivo</label>
      <textarea name="descripcion" id="descripcion">{{ old('descripcion') }}</textarea>
    </div>

    <div class="actions full">
      <button type="submit" class="btn btn-primary">Guardar movimiento</button>
      <a class="btn btn-back" href="{{ route('movimientos.index') }}">Volver</a>
    </div>
  </form>
</div>
@endsection
