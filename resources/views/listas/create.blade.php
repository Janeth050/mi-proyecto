@extends('layouts.app')

@section('title','Nueva lista')

@section('content')
<style>
  :root{--cafe:#8b5e3c;--hover:#70472e;--borde:#d9c9b3}
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:28px;font-weight:800}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px;max-width:720px}
  label{font-weight:700;color:#5c3a21}
  input{width:100%;border:1px solid var(--borde);border-radius:10px;padding:10px;margin-top:6px}
  .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-back{background:#fff;border:1px solid var(--borde);color:#70472e}.btn-back:hover{background:#f2e8db}
</style>

<h1 class="page">Nueva lista de pedido</h1>

<div class="card">
  <form method="POST" action="{{ route('listas.store') }}">
    @csrf

    <label for="comentario">Comentario (opcional)</label>
    <input type="text" id="comentario" name="comentario" value="{{ old('comentario') }}" maxlength="255" placeholder="Ej. compra semana 42">

    <div class="actions">
      <button type="submit" class="btn btn-primary"> Crear lista</button>
      <a href="{{ route('listas.index') }}" class="btn btn-back"> Volver</a>
    </div>
  </form>
</div>
@endsection
