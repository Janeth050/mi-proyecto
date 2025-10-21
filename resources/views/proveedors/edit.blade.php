@extends('layouts.app')

@section('title','Editar proveedor')

@section('content')
<style>
  :root{--cafe:#8b5e3c;--hover:#70472e;--borde:#d9c9b3}
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px;max-width:760px}
  .form{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .form .full{grid-column:1 / -1}
  label{font-weight:700;color:#5c3a21}
  input,textarea{width:100%;padding:10px;border:1px solid var(--borde);border-radius:10px}
  textarea{min-height:90px;resize:vertical}
  .errors{color:#c0392b;font-size:13px;margin-top:4px}
  .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-back{background:#fff;border:1px solid var(--borde);color:#70472e}.btn-back:hover{background:#f2e8db}
  @media(max-width:700px){ .form{grid-template-columns:1fr} }
</style>

<h1 class="page">Editar proveedor</h1>

<div class="card">
  <form class="form" action="{{ route('proveedors.update',$proveedor->id) }}" method="POST">
    @csrf @method('PUT')

    <div class="full">
      <label for="nombre">Nombre del proveedor</label>
      <input type="text" id="nombre" name="nombre" value="{{ old('nombre',$proveedor->nombre) }}" required>
      @error('nombre') <div class="errors">{{ $message }}</div> @enderror
    </div>

    <div>
      <label for="telefono">Teléfono</label>
      <input type="text" id="telefono" name="telefono" value="{{ old('telefono',$proveedor->telefono) }}">
    </div>

    <div>
      <label for="correo">Correo</label>
      <input type="email" id="correo" name="correo" value="{{ old('correo',$proveedor->correo) }}">
    </div>

    <div class="full">
      <label for="direccion">Dirección</label>
      <input type="text" id="direccion" name="direccion" value="{{ old('direccion',$proveedor->direccion) }}">
    </div>

    <div class="full">
      <label for="notas">Notas adicionales</label>
      <textarea id="notas" name="notas">{{ old('notas',$proveedor->notas) }}</textarea>
    </div>

    <div class="actions full">
      <button type="submit" class="btn btn-primary">Actualizar</button>
      <a class="btn btn-back" href="{{ route('proveedors.index') }}"> Volver</a>
    </div>
  </form>
</div>
@endsection
