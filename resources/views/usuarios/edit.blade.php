@extends('layouts.app')

@section('title','Editar usuario')

@section('content')
<style>
  :root{--cafe:#8b5e3c;--hover:#70472e;--borde:#d9c9b3}
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:28px;font-weight:800}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px;max-width:860px}
  .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  label{font-weight:700;color:#5c3a21}
  input,select{width:100%;border:1px solid var(--borde);border-radius:10px;padding:10px;margin-top:6px}
  .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-back{background:#fff;border:1px solid var(--borde);color:#70472e}.btn-back:hover{background:#f2e8db}
  .note{font-size:.85rem;color:#7a6b5f;margin-top:4px}
  @media(max-width:760px){.grid{grid-template-columns:1fr}}
</style>

<h1 class="page">Editar usuario</h1>

@if($errors->any())
  <div class="card" style="border-color:#f5c6cb;background:#f8d7da;color:#721c24;margin-bottom:12px">
    {{ $errors->first() }}
  </div>
@endif

<div class="card">
  <form method="POST" action="{{ route('usuarios.update',$usuario) }}">
    @csrf @method('PUT')

    <div class="grid">
      <div>
        <label>Nombre</label>
        <input name="name" value="{{ old('name',$usuario->name) }}" required>
      </div>

      <div>
        <label>Email</label>
        <input name="email" type="email" value="{{ old('email',$usuario->email) }}" required>
      </div>

      <div>
        <label>Rol</label>
        <select name="role" required>
          <option value="empleado" @selected(old('role',$usuario->role)==='empleado')>Empleado</option>
          <option value="admin"    @selected(old('role',$usuario->role)==='admin')>Administrador</option>
        </select>
      </div>

      <div>
        <label>Nueva contraseña (opcional)</label>
        <input name="password" type="password">
        <div class="note">Déjalo vacío si no quieres cambiar la contraseña.</div>
      </div>

      <div>
        <label>Confirmar contraseña</label>
        <input name="password_confirmation" type="password">
      </div>
    </div>

    <div class="actions">
      <button class="btn btn-primary" type="submit">Actualizar</button>
      <a href="{{ route('usuarios.index') }}" class="btn btn-back"> Cancelar</a>
    </div>
  </form>
</div>
@endsection

