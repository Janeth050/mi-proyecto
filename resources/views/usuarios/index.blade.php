@extends('layouts.app')

@section('title','Usuarios')

@section('content')
<style>
  :root{--cafe:#8b5e3c;--hover:#70472e;--texto:#5c3a21;--borde:#d9c9b3;--bad:#e74c3c}
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  .toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-danger{background:var(--bad);color:#fff}.btn-danger:hover{filter:brightness(.9)}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px}
  .flash{background:#d4edda;color:#155724;border:1px solid #c3e6cb;padding:10px;border-radius:10px;margin-bottom:10px;text-align:center}
  .flash.error{background:#f8d7da;color:#721c24;border-color:#f5c6cb}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:10px;text-align:left}
  .table th{background:var(--cafe);color:#fff}
  .table tr:nth-child(even){background:#faf6ef}
  .acciones{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
  .link{color:#8b5e3c; font-weight:700; text-decoration:none}
  .link:hover{text-decoration:underline}
  .chip{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:700}
  .chip-admin{border-color:#2c3e50;color:#2c3e50}
  .chip-emp{border-color:#7a6b5f;color:#7a6b5f}
  @media(max-width:820px){
    .table thead{display:none}
    .table tr{display:block;border:1px solid var(--borde);margin-bottom:10px;border-radius:10px;overflow:hidden}
    .table td{display:flex;justify-content:space-between;gap:12px;border:none;border-bottom:1px solid #eee}
    .table td:last-child{border-bottom:none}
    .table td::before{content:attr(data-label);font-weight:700;color:#7a6b5f}
    .acciones{justify-content:flex-end}
  }
</style>

<h1 class="page">Usuarios</h1>

@if(session('success')) <div class="flash">{{ session('success') }}</div> @endif
@if($errors->any())    <div class="flash error">{{ $errors->first() }}</div> @endif

<div class="toolbar">
  <div></div>
  <a href="{{ route('usuarios.create') }}" class="btn btn-primary"> Nuevo usuario</a>
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>Nombre</th><th>Email</th><th>Rol</th><th style="text-align:center;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($usuarios as $u)
        <tr>
          <td data-label="Nombre">{{ $u->name }}</td>
          <td data-label="Email">{{ $u->email }}</td>
          <td data-label="Rol">
            @if($u->role==='admin')
              <span class="chip chip-admin">Administrador</span>
            @else
              <span class="chip chip-emp">Empleado</span>
            @endif
          </td>
          <td data-label="Acciones">
            <div class="acciones">
              <a class="link" href="{{ route('usuarios.edit',$u) }}">Editar</a>
              <form action="{{ route('usuarios.destroy',$u) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar usuario?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">Eliminar</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" style="text-align:center;color:#7a6b5f">Sin registros</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@if(method_exists($usuarios,'links'))
  <div style="margin-top:12px">
    {{ $usuarios->links() }}
  </div>
@endif
@endsection
