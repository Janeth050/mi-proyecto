@extends('layouts.app')

@section('title','Proveedores')

@section('content')
@php
  $ES_ADMIN = (isset(auth()->user()->is_admin) && auth()->user()->is_admin) || (strtolower(auth()->user()->role ?? auth()->user()->rol ?? '') === 'admin');
@endphp

<style>
  :root{
    --cafe:#8b5e3c; --hover:#70472e; --texto:#5c3a21; --borde:#d9c9b3; --bad:#e74c3c;
  }
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  .toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:12px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none;transition:transform .08s ease, box-shadow .12s ease}
  .btn:hover{transform:translateY(-1px)}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-ghost{background:#fff;border:1px solid var(--borde);color:#70472e}
  .btn-ghost:hover{background:#f3eadd}
  .btn-danger{background:var(--bad);color:#fff}.btn-danger:hover{filter:brightness(.92)}
  .btn-gray{background:#6c757d;color:#fff}.btn-gray:hover{filter:brightness(.95)}
  .btn-xs{padding:6px 10px;border-radius:10px;font-size:12px}
  .pill{padding:8px 12px;border-radius:12px;border:1px solid var(--borde);background:#fff;color:#70472e;font-weight:700;text-decoration:none}
  .pill:hover{background:#f2e8db}

  .card{background:#fff;border:1px solid var(--borde);border-radius:16px;box-shadow:0 10px 28px rgba(0,0,0,.08);padding:16px}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:10px;text-align:center}
  .table th{background:#8b5e3c;color:#fff}
  .table tr:nth-child(even){background:#faf6ef}

  .search{display:flex;gap:8px;flex-wrap:wrap}
  .search input{border:1px solid var(--borde);border-radius:12px;padding:10px 12px;min-width:260px}

  /* ===== Modales (glass) ===== */
  .overlay{position:fixed;inset:0;background:rgba(0,0,0,.25);z-index:90;display:none;align-items:center;justify-content:center;padding:14px}
  .overlay.show{display:flex}
  .modal-card{
    width:min(760px,96vw);
    background:rgba(255,255,255,.78);
    backdrop-filter: blur(10px) saturate(140%);
    -webkit-backdrop-filter: blur(10px) saturate(140%);
    border:1px solid rgba(255,255,255,.6);
    border-radius:20px;
    box-shadow:0 18px 50px rgba(0,0,0,.25);
    padding:18px;
    position:relative;
    animation: pop .18s ease-out;
  }
  @keyframes pop{from{transform:scale(.98);opacity:.85}to{transform:scale(1);opacity:1}}
  .modal-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
  .modal-head h3{margin:0;color:var(--texto);letter-spacing:.2px}
  .close-x{background:transparent;border:none;font-size:22px;line-height:16px;cursor:pointer;color:#555}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .grid2 label{font-weight:700;color:#7a6b5f}
  .grid2 input,.grid2 textarea{
    width:100%;border:1px solid var(--borde);border-radius:12px;padding:10px 12px;background:#fff;
  }
  .muted{color:#7a6b5f}

  @media(max-width:760px){
    .table thead{display:none}
    .table tr{display:block;border:1px solid var(--borde);margin-bottom:10px;border-radius:12px;overflow:hidden}
    .table td{display:flex;justify-content:space-between;gap:12px;border:none;border-bottom:1px solid #eee}
    .table td:last-child{border-bottom:none}
    .table td::before{content:attr(data-label);font-weight:700;color:#7a6b5f}
    .grid2{grid-template-columns:1fr}
  }
</style>

<h1 class="page">Proveedores</h1>

<div class="toolbar">
  <form class="search" action="{{ route('proveedors.index') }}" method="GET">
    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Buscar nombre, teléfono, correo o dirección">
    <button class="btn btn-primary" type="submit">Buscar</button>
    @if(($q ?? '')!=='')
      <a class="btn btn-gray" href="{{ route('proveedors.index') }}">Limpiar</a>
    @endif
  </form>

  @if($ES_ADMIN)
    <button class="btn btn-primary" id="btnNuevo">Nuevo proveedor</button>
  @endif
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>#</th>
        <th>Nombre</th>
        <th>Teléfono</th>
        <th>Correo</th>
        <th>Dirección</th>
        <th>Notas</th>
        <th style="min-width:240px">Acciones</th>
      </tr>
    </thead>
    <tbody id="tbody">
      @forelse($proveedors as $prov)
      <tr data-id="{{ $prov->id }}">
        <td data-label="#">{{ $prov->id }}</td>
        <td data-label="Nombre" class="nombre">{{ $prov->nombre }}</td>
        <td data-label="Teléfono" class="telefono">{{ $prov->telefono ?? '—' }}</td>
        <td data-label="Correo" class="correo">{{ $prov->correo ?? '—' }}</td>
        <td data-label="Dirección" class="direccion">{{ $prov->direccion ?? '—' }}</td>
        <td data-label="Notas" class="notas">{{ $prov->notas ?? '—' }}</td>
        <td data-label="Acciones" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
          <button class="pill" data-ver="{{ $prov->id }}">Ver</button>
          @if($ES_ADMIN)
            <button class="btn btn-primary btn-xs" data-editar="{{ $prov->id }}">Editar</button>
            <button class="btn btn-danger btn-xs" data-eliminar="{{ $prov->id }}">Eliminar</button>
          @endif
        </td>
      </tr>
      @empty
        <tr><td colspan="7" style="text-align:center;color:#7a6b5f">No hay proveedores.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- ================== Modales ================== --}}
{{-- Crear --}}
<div class="overlay" id="ovCrear">
  <div class="modal-card">
    <div class="modal-head">
      <h3>Nuevo proveedor</h3>
      <button class="close-x" data-close>&times;</button>
    </div>
    <form id="form-crear" class="grid2">
      @csrf
      <label style="grid-column:1/-1">Nombre
        <input type="text" name="nombre" required maxlength="255">
      </label>
      <label>Teléfono
        <input type="text" name="telefono" maxlength="50" placeholder="Opcional">
      </label>
      <label>Correo
        <input type="email" name="correo" maxlength="255" placeholder="Opcional">
      </label>
      <label style="grid-column:1/-1">Dirección
        <input type="text" name="direccion" maxlength="255" placeholder="Opcional">
      </label>
      <label style="grid-column:1/-1">Notas
        <textarea name="notas" rows="3" placeholder="Opcional"></textarea>
      </label>
      <div style="grid-column:1/-1;display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
        <button type="button" class="btn btn-ghost" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- Editar --}}
<div class="overlay" id="ovEditar">
  <div class="modal-card">
    <div class="modal-head">
      <h3>Editar proveedor</h3>
      <button class="close-x" data-close>&times;</button>
    </div>
    <form id="form-editar" class="grid2">
      @csrf @method('PUT')
      <input type="hidden" name="id" id="edit-id">
      <label style="grid-column:1/-1">Nombre
        <input type="text" name="nombre" id="edit-nombre" required maxlength="255">
      </label>
      <label>Teléfono
        <input type="text" name="telefono" id="edit-telefono" maxlength="50" placeholder="Opcional">
      </label>
      <label>Correo
        <input type="email" name="correo" id="edit-correo" maxlength="255" placeholder="Opcional">
      </label>
      <label style="grid-column:1/-1">Dirección
        <input type="text" name="direccion" id="edit-direccion" maxlength="255" placeholder="Opcional">
      </label>
      <label style="grid-column:1/-1">Notas
        <textarea name="notas" id="edit-notas" rows="3" placeholder="Opcional"></textarea>
      </label>
      <div style="grid-column:1/-1;display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
        <button type="button" class="btn btn-ghost" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary">Actualizar</button>
      </div>
    </form>
  </div>
</div>

{{-- Ver --}}
<div class="overlay" id="ovVer">
  <div class="modal-card">
    <div class="modal-head">
      <h3>Detalle de proveedor</h3>
      <button class="close-x" data-close>&times;</button>
    </div>
    <div id="ver-body" class="muted"></div>
    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
      <button class="btn btn-ghost" data-close>Cerrar</button>
    </div>
  </div>
</div>

<script>
(function(){
  const $ = s => document.querySelector(s);
  const $$ = s => Array.from(document.querySelectorAll(s));
  const token = '{{ csrf_token() }}';
  const isAdmin = {{ $ES_ADMIN ? 'true' : 'false' }};

  // ===== helpers modal =====
  function open(id){ $(id).classList.add('show'); }
  function closeAll(){ $$('.overlay').forEach(o => o.classList.remove('show')); }
  $$('.overlay [data-close]').forEach(btn => btn.addEventListener('click', closeAll));
  window.addEventListener('keydown', e => { if(e.key==='Escape') closeAll(); });

  // ===== crear =====
  $('#btnNuevo')?.addEventListener('click', () => open('#ovCrear'));

  $('#form-crear')?.addEventListener('submit', function(e){
    e.preventDefault();
    const fd = new FormData(this);
    fetch(`{{ route('proveedors.store') }}`, {
      method:'POST',
      headers:{ 'X-CSRF-TOKEN': token, 'Accept':'application/json' },
      body: fd
    }).then(r=>r.json()).then(res=>{
      if(res.ok){
        prependRow(res.proveedor);
        closeAll(); this.reset();
      } else alert(res.message || 'Error al crear proveedor');
    }).catch(()=>alert('Error de red'));
  });

  // ===== ver =====
  function ver(id){
    fetch(`/proveedors/${id}`, { headers:{ 'Accept':'application/json' }})
      .then(r=>r.json()).then(res=>{
        if(!res.ok) return;
        const p = res.proveedor;
        $('#ver-body').innerHTML = `
          <ul style="list-style:disc;padding-left:18px;line-height:1.9">
            <li><strong>Nombre:</strong> ${p.nombre ?? ''}</li>
            <li><strong>Teléfono:</strong> ${p.telefono ?? '—'}</li>
            <li><strong>Correo:</strong> ${p.correo ?? '—'}</li>
            <li><strong>Dirección:</strong> ${p.direccion ?? '—'}</li>
            <li><strong>Notas:</strong> ${p.notas ?? '—'}</li>
          </ul>
        `;
        open('#ovVer');
      });
  }

  // ===== editar (cargar) =====
  function editar(id){
    fetch(`/proveedors/${id}/edit`, { headers:{ 'Accept':'application/json' }})
      .then(r=>r.json()).then(res=>{
        if(!res.ok) return alert('No se pudo cargar');
        const p = res.proveedor;
        $('#edit-id').value = p.id;
        $('#edit-nombre').value = p.nombre ?? '';
        $('#edit-telefono').value = p.telefono ?? '';
        $('#edit-correo').value = p.correo ?? '';
        $('#edit-direccion').value = p.direccion ?? '';
        $('#edit-notas').value = p.notas ?? '';
        open('#ovEditar');
      });
  }

  // ===== editar (guardar) =====
  $('#form-editar')?.addEventListener('submit', function(e){
    e.preventDefault();
    const id = $('#edit-id').value;
    const fd = new FormData(this); // ya lleva _method=PUT
    fetch(`/proveedors/${id}`, {
      method:'POST',
      headers:{ 'X-CSRF-TOKEN': token, 'Accept':'application/json' },
      body: fd
    }).then(r=>r.json()).then(res=>{
      if(res.ok){
        updateRow(res.proveedor);
        closeAll();
      } else alert(res.message || 'Error al actualizar');
    }).catch(()=>alert('Error de red'));
  });

  // ===== eliminar =====
  function eliminar(id){
    if(!confirm('¿Eliminar proveedor?')) return;
    fetch(`/proveedors/${id}`, {
      method:'POST',
      headers:{ 'X-CSRF-TOKEN': token, 'Accept':'application/json' },
      body: new URLSearchParams({ _method:'DELETE' })
    }).then(r=>r.json()).then(res=>{
      if(res.ok){
        document.querySelector(`tr[data-id="${id}"]`)?.remove();
      } else alert(res.message || 'No se pudo eliminar');
    }).catch(()=>alert('Error de red'));
  }

  // ===== tabla (helpers DOM) =====
  function rowHTML(p){
    return `
      <td data-label="#">${p.id}</td>
      <td data-label="Nombre" class="nombre">${p.nombre ?? ''}</td>
      <td data-label="Teléfono" class="telefono">${p.telefono ?? '—'}</td>
      <td data-label="Correo" class="correo">${p.correo ?? '—'}</td>
      <td data-label="Dirección" class="direccion">${p.direccion ?? '—'}</td>
      <td data-label="Notas" class="notas">${p.notas ?? '—'}</td>
      <td data-label="Acciones" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
        <button class="pill" data-ver="${p.id}">Ver</button>
        ${isAdmin ? `
          <button class="btn btn-primary btn-xs" data-editar="${p.id}">Editar</button>
          <button class="btn btn-danger btn-xs" data-eliminar="${p.id}">Eliminar</button>
        ` : ``}
      </td>
    `;
  }

  function prependRow(p){
    const tr = document.createElement('tr');
    tr.setAttribute('data-id', p.id);
    tr.innerHTML = rowHTML(p);
    $('#tbody').prepend(tr);
    bindRow(tr);
  }

  function updateRow(p){
    const tr = document.querySelector(`tr[data-id="${p.id}"]`);
    if(!tr) return prependRow(p);
    tr.innerHTML = rowHTML(p);
    bindRow(tr);
  }

  function bindRow(tr){
    const id = tr.getAttribute('data-id');
    tr.querySelector(`[data-ver]`)?.addEventListener('click', () => ver(id));
    tr.querySelector(`[data-editar]`)?.addEventListener('click', () => editar(id));
    tr.querySelector(`[data-eliminar]`)?.addEventListener('click', () => eliminar(id));
  }

  // Bind inicial
  $$('#tbody tr').forEach(bindRow);
})();
</script>
@endsection
