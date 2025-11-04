@extends('layouts.app')

@section('title','Usuarios')

@section('content')
@php
  $ES_ADMIN = (isset(auth()->user()->is_admin) && auth()->user()->is_admin)
              || (strtolower(auth()->user()->role ?? auth()->user()->rol ?? '') === 'admin');
@endphp

<style>
  :root{ --cafe:#8b5e3c; --hover:#70472e; --texto:#5c3a21; --borde:#d9c9b3; --bad:#e74c3c; }
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  .toolbar{display:flex;justify-content:flex-end;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:12px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none;transition:transform .08s ease}
  .btn:hover{transform:translateY(-1px)}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-ghost{background:#fff;border:1px solid var(--borde);color:#70472e}.btn-ghost:hover{background:#f3eadd}
  .btn-danger{background:var(--bad);color:#fff}.btn-danger:hover{filter:brightness(.92)}
  .btn-xs{padding:6px 10px;border-radius:10px;font-size:12px}
  .pill{padding:8px 12px;border-radius:12px;border:1px solid var(--borde);background:#fff;color:#70472e;font-weight:700;text-decoration:none}
  .card{background:#fff;border:1px solid var(--borde);border-radius:16px;box-shadow:0 10px 28px rgba(0,0,0,.08);padding:16px}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:10px;text-align:center}
  .table th{background:#8b5e3c;color:#fff}
  .table tr:nth-child(even){background:#faf6ef}

  /* overlay */
  .overlay{position:fixed;inset:0;background:rgba(0,0,0,.25);z-index:90;display:none;align-items:center;justify-content:center;padding:14px}
  .overlay.show{display:flex}
  .modal-card{width:min(720px,96vw);background:rgba(255,255,255,.78);backdrop-filter: blur(10px) saturate(140%);-webkit-backdrop-filter: blur(10px) saturate(140%);border:1px solid rgba(255,255,255,.6);border-radius:20px;box-shadow:0 18px 50px rgba(0,0,0,.25);padding:18px;animation: pop .18s ease-out;}
  @keyframes pop{from{transform:scale(.98);opacity:.85}to{transform:scale(1);opacity:1}}
  .modal-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .grid2 label{font-weight:700;color:#7a6b5f}
  .grid2 input,.grid2 select{width:100%;border:1px solid var(--borde);border-radius:12px;padding:10px 12px;background:#fff;}
  @media(max-width:760px){.table thead{display:none}.table tr{display:block;border:1px solid var(--borde);margin-bottom:10px;border-radius:12px;overflow:hidden}
    .table td{display:flex;justify-content:space-between;gap:12px;border:none;border-bottom:1px solid #eee}
    .table td:last-child{border-bottom:none}.table td::before{content:attr(data-label);font-weight:700;color:#7a6b5f}
    .grid2{grid-template-columns:1fr}}
</style>

<h1 class="page">Usuarios</h1>

<div class="toolbar">
  @if($ES_ADMIN)
    <button class="btn btn-primary" data-open="ovCrearUser">Nuevo usuario</button>
  @endif
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr><th>#</th><th>Nombre</th><th>Email</th><th>Rol</th><th style="min-width:220px">Acciones</th></tr>
    </thead>
    <tbody id="tbody">
      @forelse($usuarios as $u)
      <tr data-id="{{ $u->id }}" data-name="{{ $u->name }}" data-email="{{ $u->email }}" data-role="{{ $u->role }}">
        <td data-label="#">{{ $u->id }}</td>
        <td data-label="Nombre">{{ $u->name }}</td>
        <td data-label="Email">{{ $u->email }}</td>
        <td data-label="Rol">
          @if($u->role === 'admin') <span class="pill">Administrador</span>
          @else <span class="pill">Empleado</span> @endif
        </td>
        <td data-label="Acciones" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
          <button class="btn btn-primary btn-xs" data-edit="{{ $u->id }}">Editar</button>
          <form method="POST" action="{{ route('usuarios.destroy',$u->id) }}" onsubmit="return confirm('¿Eliminar usuario?')" style="display:inline-block">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-xs" type="submit">Eliminar</button>
          </form>
        </td>
      </tr>
      @empty
        <tr><td colspan="5" style="text-align:center;color:#7a6b5f">No hay usuarios.</td></tr>
      @endforelse
    </tbody>
  </table>
  <div style="margin-top:12px">
    {{ $usuarios->links() }}
  </div>
</div>

{{-- Crear usuario --}}
@if($ES_ADMIN)
<div class="overlay" id="ovCrearUser">
  <div class="modal-card">
    <div class="modal-head">
      <h3>Nuevo usuario</h3>
      <button class="btn btn-ghost" data-close>&times;</button>
    </div>
    <form class="grid2" method="POST" action="{{ route('usuarios.store') }}">
      @csrf
      <label style="grid-column:1/-1">Nombre
        <input type="text" name="name" required maxlength="255">
      </label>
      <label style="grid-column:1/-1">Email
        <input type="email" name="email" required maxlength="255">
      </label>
      <label>Rol
        <select name="role" required>
          <option value="empleado" selected>Empleado</option>
          <option value="admin">Administrador</option>
        </select>
      </label>
      <label>Contraseña
        <input type="password" name="password" required minlength="6">
      </label>
      <label style="grid-column:1/-1">Confirmar contraseña
        <input type="password" name="password_confirmation" required minlength="6">
      </label>
      <div style="grid-column:1/-1;display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
        <button type="button" class="btn btn-ghost" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary">Crear</button>
      </div>
    </form>
  </div>
</div>
@endif

{{-- Un solo modal para EDITAR --}}
<div class="overlay" id="ovEditUser">
  <div class="modal-card">
    <div class="modal-head">
      <h3>Editar usuario</h3>
      <button class="btn btn-ghost" data-close>&times;</button>
    </div>
    <form id="form-edit-user" class="grid2" method="POST">
      @csrf @method('PUT')
      <label style="grid-column:1/-1">Nombre
        <input type="text" name="name" id="eu-name" required maxlength="255">
      </label>
      <label style="grid-column:1/-1">Email
        <input type="email" name="email" id="eu-email" required maxlength="255">
      </label>
      <label>Rol
        <select name="role" id="eu-role" required>
          <option value="admin">Administrador</option>
          <option value="empleado">Empleado</option>
        </select>
      </label>
      <label>Contraseña (opcional)
        <input type="password" name="password" id="eu-pass" placeholder="Dejar en blanco para mantener">
      </label>
      <label style="grid-column:1/-1">Confirmar contraseña
        <input type="password" name="password_confirmation" id="eu-pass2" placeholder="Si cambias la contraseña, confirma aquí">
      </label>
      <div style="grid-column:1/-1;display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
        <button type="button" class="btn btn-ghost" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary">Actualizar</button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  const $  = s => document.querySelector(s);
  const $$ = s => Array.from(document.querySelectorAll(s));

  function openModal(id){ $(id).classList.add('show'); }
  function closeModal(btn){ btn.closest('.overlay')?.classList.remove('show'); }
  document.addEventListener('click', (e)=>{
    const openBtn = e.target.closest('[data-open]');
    const closeBtn= e.target.closest('[data-close]');
    const editBtn = e.target.closest('[data-edit]');
    if(openBtn){ openModal(`#${openBtn.getAttribute('data-open')}`); }
    if(closeBtn){ closeModal(closeBtn); }
    if(editBtn){
      const id = editBtn.getAttribute('data-edit');
      const tr = document.querySelector(`tr[data-id="${id}"]`);
      if(!tr) return;
      // Prellenar
      $('#eu-name').value  = tr.getAttribute('data-name')  || '';
      $('#eu-email').value = tr.getAttribute('data-email') || '';
      $('#eu-role').value  = tr.getAttribute('data-role')  || 'empleado';
      // action del form
      const f = $('#form-edit-user');
      f.action = `{{ url('/usuarios') }}/${id}`;
      $('#eu-pass').value = ''; $('#eu-pass2').value = '';
      openModal('#ovEditUser');
    }
  });
  window.addEventListener('keydown', e => {
    if(e.key==='Escape') document.querySelectorAll('.overlay.show').forEach(o=>o.classList.remove('show'));
  });
})();
</script>
@endsection
