@extends('layouts.app')

@section('title','Productos')

@section('content')
@php
  $ES_ADMIN = (isset(auth()->user()->is_admin) && auth()->user()->is_admin)
              || (strtolower(auth()->user()->role ?? auth()->user()->rol ?? '') === 'admin');
@endphp

<style>
  :root{ --cafe:#8b5e3c; --hover:#70472e; --texto:#5c3a21; --borde:#d9c9b3; --bad:#e74c3c; --ok:#2ecc71; --warn:#f1c40f; }
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  .toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:12px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none;transition:transform .08s ease, box-shadow .12s ease}
  .btn:hover{transform:translateY(-1px)}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-ghost{background:#fff;border:1px solid var(--borde);color:#70472e}.btn-ghost:hover{background:#f3eadd}
  .btn-danger{background:var(--bad);color:#fff}.btn-danger:hover{filter:brightness(.92)}
  .btn-gray{background:#6c757d;color:#fff}.btn-gray:hover{filter:brightness(.95)}
  .btn-xs{padding:6px 10px;border-radius:10px;font-size:12px}
  .pill{padding:8px 12px;border-radius:12px;border:1px solid var(--borde);background:#fff;color:#70472e;font-weight:700}
  .pill:hover{background:#f2e8db}
  .card{background:#fff;border:1px solid var(--borde);border-radius:16px;box-shadow:0 10px 28px rgba(0,0,0,.08);padding:16px}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:10px;text-align:center}
  .table th{background:#8b5e3c;color:#fff}
  .table tr:nth-child(even){background:#faf6ef}
  .tag-alerta{display:inline-flex;align-items:center;gap:6px;padding:4px 8px;border-radius:999px;border:1px solid #f5c6cb;color:#a32121;background:#fdecec;font-weight:700;font-size:12px}
  .search{display:flex;gap:8px;flex-wrap:wrap}
  .search input{border:1px solid var(--borde);border-radius:12px;padding:10px 12px;min-width:260px}

  .overlay{position:fixed;inset:0;background:rgba(0,0,0,.25);z-index:9999;display:none;align-items:center;justify-content:center;padding:14px}
  .overlay.show{display:flex}
  .modal-card{width:min(820px,96vw);background:rgba(255,255,255,.78);backdrop-filter: blur(10px) saturate(140%);-webkit-backdrop-filter: blur(10px) saturate(140%);border:1px solid rgba(255,255,255,.6);border-radius:20px; box-shadow:0 18px 50px rgba(0,0,0,.25);padding:18px; position:relative; animation: pop .18s ease-out;}
  @keyframes pop{from{transform:scale(.98);opacity:.85}to{transform:scale(1);opacity:1}}
  .modal-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
  .modal-head h3{margin:0;color:var(--texto);letter-spacing:.2px}
  .close-x{background:transparent;border:none;font-size:22px;line-height:16px;cursor:pointer;color:#555}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .grid2 label{font-weight:700;color:#7a6b5f}
  .grid2 input,.grid2 select{width:100%;border:1px solid var(--borde);border-radius:12px;padding:10px 12px;background:#fff;}
  @media(max-width:760px){.table thead{display:none}.table tr{display:block;border:1px solid var(--borde);margin-bottom:10px;border-radius:12px;overflow:hidden}
    .table td{display:flex;justify-content:space-between;gap:12px;border:none;border-bottom:1px solid #eee}
    .table td:last-child{border-bottom:none}.table td::before{content:attr(data-label);font-weight:700;color:#7a6b5f}
    .grid2{grid-template-columns:1fr}}
</style>

<h1 class="page">Inventario de Productos</h1>

<div class="toolbar">
  <form class="search" action="{{ route('productos.index') }}" method="GET">
    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Filtrar (código, nombre, categoría, unidad)">
    <button class="btn btn-primary" type="submit">Buscar</button>
    @if(($q ?? '')!=='')
      <a class="btn btn-gray" href="{{ route('productos.index') }}" type="button">Limpiar</a>
    @endif
  </form>
  @if($ES_ADMIN)
    <button class="btn btn-primary" id="btnNuevo" type="button">Nuevo producto</button>
  @endif
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>Código</th><th>Nombre</th><th>Unidad</th><th>Categoría</th><th>Existencias</th><th>Stock mínimo</th><th style="min-width:260px">Acciones</th>
      </tr>
    </thead>
    <tbody id="tbody">
      @forelse($productos as $p)
      @php $alerta = (int)$p->existencias < (int)$p->stock_minimo; @endphp
      <tr data-id="{{ $p->id }}" data-alerta="{{ $alerta ? '1':'0' }}">
        <td class="codigo" data-label="Código">{{ $p->codigo }}</td>
        <td class="nombre" data-label="Nombre">
          {{ $p->nombre }} @if($alerta)<span class="tag-alerta" title="Bajo stock">⚠ Bajo</span>@endif
        </td>
        <td class="unidad" data-label="Unidad">{{ $p->unidad->descripcion ?? '-' }}</td>
        <td class="categoria" data-label="Categoría">{{ $p->categoria->nombre ?? '-' }}</td>
        <td class="existencias" data-label="Existencias">{{ $p->existencias }}</td>
        <td class="stock_minimo" data-label="Stock mínimo">{{ $p->stock_minimo }}</td>
        <td data-label="Acciones" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
          <button class="pill" data-ver="{{ $p->id }}" type="button">Ver</button>
          <a class="pill" href="{{ route('kardex.producto',$p->id) }}">Kardex</a>
          @if($ES_ADMIN)
            <button class="btn btn-primary btn-xs" data-editar="{{ $p->id }}" type="button">Editar</button>
            <button class="btn btn-danger btn-xs" data-eliminar="{{ $p->id }}" type="button">Eliminar</button>
            @if($alerta)
              <button class="btn btn-ghost btn-xs" data-addlist="{{ $p->id }}" type="button">Agregar a lista</button>
            @endif
          @endif
        </td>
      </tr>
      @empty
        <tr><td colspan="7" style="text-align:center;color:#7a6b5f">No hay productos registrados.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- ===== Modal CREAR ===== --}}
<div class="overlay" id="ovCrear">
  <div class="modal-card">
    <div class="modal-head"><h3>Nuevo producto</h3><button class="close-x" data-close type="button">&times;</button></div>
    <form id="form-crear" class="grid2">
      @csrf
      <label>Código  <input type="text" name="codigo" required maxlength="64"></label>
      <label>Nombre  <input type="text" name="nombre" required maxlength="255"></label>

      <label>Unidad
        <div style="display:flex; gap:8px">
          <select name="unidad_id" id="create-unidad" required style="flex:1">
            <option value="">— Selecciona —</option>
            @foreach(\App\Models\Unidad::orderBy('descripcion')->get() as $u)
              <option value="{{ $u->id }}">{{ $u->descripcion }}</option>
            @endforeach
          </select>
          @if($ES_ADMIN)
            <button type="button" class="btn btn-ghost btn-xs" id="btnNuevaUnidad">+ Unidad</button>
            <button type="button" class="btn btn-danger btn-xs" id="btnBorrarUnidad">Eliminar</button>
          @endif
        </div>
      </label>

      <label>Categoría
        <div style="display:flex; gap:8px">
          <select name="categoria_id" id="create-categoria" style="flex:1">
            <option value="">— Sin categoría —</option>
            @foreach(\App\Models\Categoria::orderBy('nombre')->get() as $c)
              <option value="{{ $c->id }}">{{ $c->nombre }}</option>
            @endforeach
          </select>
          @if($ES_ADMIN)
            <button type="button" class="btn btn-ghost btn-xs" id="btnNuevaCategoria">+ Categoría</button>
            <button type="button" class="btn btn-danger btn-xs" id="btnBorrarCategoria">Eliminar</button>
          @endif
        </div>
      </label>

      <label>Existencias   <input type="number" name="existencias" min="0" required></label>
      <label>Stock mínimo  <input type="number" name="stock_minimo" min="0" required></label>
      <label>Costo promedio <input type="number" step="0.0001" min="0" name="costo_promedio" placeholder="Opcional"></label>
      <label>Presentación   <input type="text" name="presentacion_detalle" maxlength="255" placeholder="Ej: Costal 50 kg"></label>

      <div style="grid-column:1/-1;display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
        <button type="button" class="btn btn-ghost" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- ===== Modal EDITAR ===== --}}
<div class="overlay" id="ovEditar">
  <div class="modal-card">
    <div class="modal-head"><h3>Editar producto</h3><button class="close-x" data-close type="button">&times;</button></div>
    <form id="form-editar" class="grid2">
      @csrf @method('PUT')
      <input type="hidden" name="id" id="edit-id">
      <label>Código  <input type="text" name="codigo" id="edit-codigo" required maxlength="64"></label>
      <label>Nombre  <input type="text" name="nombre" id="edit-nombre" required maxlength="255"></label>

      <label>Unidad
        <div style="display:flex; gap:8px">
          <select name="unidad_id" id="edit-unidad" required style="flex:1">
            <option value="">— Selecciona —</option>
            @foreach(\App\Models\Unidad::orderBy('descripcion')->get() as $u)
              <option value="{{ $u->id }}">{{ $u->descripcion }}</option>
            @endforeach
          </select>
          @if($ES_ADMIN)
            <button type="button" class="btn btn-ghost btn-xs" id="btnNuevaUnidad2">+ Unidad</button>
            <button type="button" class="btn btn-danger btn-xs" id="btnBorrarUnidad2">Eliminar</button>
          @endif
        </div>
      </label>

      <label>Categoría
        <div style="display:flex; gap:8px">
          <select name="categoria_id" id="edit-categoria" style="flex:1">
            <option value="">— Sin categoría —</option>
            @foreach(\App\Models\Categoria::orderBy('nombre')->get() as $c)
              <option value="{{ $c->id }}">{{ $c->nombre }}</option>
            @endforeach
          </select>
          @if($ES_ADMIN)
            <button type="button" class="btn btn-ghost btn-xs" id="btnNuevaCategoria2">+ Categoría</button>
            <button type="button" class="btn btn-danger btn-xs" id="btnBorrarCategoria2">Eliminar</button>
          @endif
        </div>
      </label>

      <label>Existencias   <input type="number" name="existencias" id="edit-existencias" min="0" required></label>
      <label>Stock mínimo  <input type="number" name="stock_minimo" id="edit-stock" min="0" required></label>
      <label>Costo promedio <input type="number" step="0.0001" min="0" name="costo_promedio" id="edit-costo" placeholder="Opcional"></label>
      <label>Presentación   <input type="text" name="presentacion_detalle" id="edit-pres" maxlength="255" placeholder="Ej: Costal 50 kg"></label>

      <div style="grid-column:1/-1;display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
        <button type="button" class="btn btn-ghost" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary">Actualizar</button>
      </div>
    </form>
  </div>
</div>

{{-- ===== Modal VER ===== --}}
<div class="overlay" id="ovVer">
  <div class="modal-card">
    <div class="modal-head"><h3>Detalle de producto</h3><button class="close-x" data-close type="button">&times;</button></div>
    <div id="ver-body" class="muted"></div>
    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
      <button class="btn btn-ghost" data-close type="button">Cerrar</button>
    </div>
  </div>
</div>

{{-- ===== Modal ELIMINAR ===== --}}
<div class="overlay" id="ovDelete">
  <div class="modal-card">
    <div class="modal-head">
      <h3>Confirmar eliminación</h3>
      <button class="close-x" data-close type="button">&times;</button>
    </div>
    <p>¿Seguro que deseas eliminar el producto <strong id="del-name">este</strong>? Esta acción no se puede deshacer.</p>
    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:10px">
      <button class="btn btn-ghost" data-close type="button">Cancelar</button>
      <button class="btn btn-danger" id="btnConfirmDelete" type="button">Eliminar</button>
    </div>
  </div>
</div>

{{-- ===== Sub-modal Unidad ===== --}}
<div class="overlay" id="ovUnidad">
  <div class="modal-card">
    <div class="modal-head"><h3>Nueva unidad</h3><button class="close-x" data-close type="button">&times;</button></div>
    <form id="form-unidad" class="grid2">
      @csrf
      <label style="grid-column:1/-1">Descripción <input type="text" name="descripcion" maxlength="100" required></label>
      <div style="grid-column:1/-1;display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
        <button type="button" class="btn btn-ghost" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- ===== Sub-modal Categoría ===== --}}
<div class="overlay" id="ovCategoria">
  <div class="modal-card">
    <div class="modal-head"><h3>Nueva categoría</h3><button class="close-x" data-close type="button">&times;</button></div>
    <form id="form-categoria" class="grid2">
      @csrf
      <label style="grid-column:1/-1">Nombre <input type="text" name="nombre" maxlength="100" required></label>
      <div style="grid-column:1/-1;display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
        <button type="button" class="btn btn-ghost" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- ===== MODAL: Agregar a nueva lista ===== --}}
<div class="overlay" id="ovAddList">
  <div class="modal-card">
    <div class="modal-head">
      <h3>Agregar a nueva lista</h3>
      <button class="close-x" data-close>&times;</button>
    </div>
    <form id="form-addlist" class="grid2">
      @csrf
      <input type="hidden" name="producto_id" id="addlist-prod">
      <label>Cantidad
        <input type="number" name="cantidad" id="addlist-cant" min="1" required>
      </label>
      <label>Proveedor (opcional)
        <select name="proveedor_id" id="addlist-prov">
          <option value="">—</option>
          @foreach(\App\Models\Proveedor::orderBy('nombre')->get() as $prov)
            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
          @endforeach
        </select>
      </label>
      <label>Precio estimado (opcional)
        <input type="number" name="precio_estimado" id="addlist-precio" step="0.01" min="0">
      </label>
      <div style="grid-column:1/-1;display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
        <button type="button" class="btn btn-ghost" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  const $  = s => document.querySelector(s);
  const $$ = s => Array.from(document.querySelectorAll(s));
  const token   = '{{ csrf_token() }}';
  const isAdmin = {{ $ES_ADMIN ? 'true' : 'false' }};
  const def = (v,d='') => (v===undefined||v===null?d:v);

  // ===== Helpers modal
  function openModal(sel){ const el=$(sel); if(el) el.classList.add('show'); }
  function closeModal(fromEl){
    let p = fromEl?.closest ? fromEl.closest('.overlay') : null;
    if(!p){ let n = fromEl?.parentNode || null; while(n && !(n.classList && n.classList.contains('overlay'))) n = n.parentNode; p = n; }
    if(p) p.classList.remove('show');
  }
  window.addEventListener('keydown', e => {
    if(e.key==='Escape'){ const opens=$$('.overlay.show'); if(opens.length) opens.at(-1).classList.remove('show'); }
  });
  $$('.overlay [data-close]').forEach(btn => btn.addEventListener('click', () => closeModal(btn)));

  // Abrir modales
  $('#btnNuevo')?.addEventListener('click', () => openModal('#ovCrear'));
  $('#btnNuevaUnidad')?.addEventListener('click', () => openModal('#ovUnidad'));
  $('#btnNuevaUnidad2')?.addEventListener('click', () => openModal('#ovUnidad'));
  $('#btnNuevaCategoria')?.addEventListener('click', () => openModal('#ovCategoria'));
  $('#btnNuevaCategoria2')?.addEventListener('click', () => openModal('#ovCategoria'));

  // ===== Fetch helpers
  function post(url, body){
    return fetch(url,{method:'POST',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'},body})
      .then(r=>r.json().catch(()=>({})).then(js=>({ok:r.ok && js && js.ok===true, js})));
  }
  function del(url){
    return fetch(url,{method:'DELETE',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}})
      .then(r=>r.json().catch(()=>({})).then(js=>({ok:r.ok && js && js.ok===true, js})));
  }

  // ===== Crear
  const fCrear = $('#form-crear');
  fCrear?.addEventListener('submit', e=>{
    e.preventDefault();
    post("{{ route('productos.store') }}", new FormData(fCrear)).then(res=>{
      if(!res.ok){
        const msg = res.js?.errors ? Object.values(res.js.errors).flat().join('\n') : (res.js.message||'Error al crear');
        return alert(msg);
      }
      addOrUpdateRow(res.js.producto);
      closeModal(fCrear); fCrear.reset();
    }).catch(()=>alert('Error de red'));
  });

  // ===== Delegación tabla
  const tbody = $('#tbody');
  let deleteId = null;
  tbody?.addEventListener('click', ev=>{
    const btn = ev.target.closest('button,a'); if(!btn) return;
    const tr  = ev.target.closest('tr[data-id]'); const id = tr?.getAttribute('data-id'); if(!id) return;

    if(btn.hasAttribute('data-ver'))      return ver(id);
    if(btn.hasAttribute('data-editar'))   return editar(id);
    if(btn.hasAttribute('data-eliminar')) {
      deleteId = id;
      $('#del-name').textContent = tr.querySelector('.nombre')?.textContent?.trim() || 'este producto';
      return openModal('#ovDelete');
    }
    if(btn.hasAttribute('data-addlist'))  return addToList(id);
  });

  // Confirmar eliminar
  $('#btnConfirmDelete')?.addEventListener('click', ()=>{
    if(!deleteId) return;
    post('/productos/'+encodeURIComponent(deleteId), new URLSearchParams('_method=DELETE')).then(res=>{
      if(res.ok){
        document.querySelector(`tr[data-id="${deleteId}"]`)?.remove();
      } else {
        alert(res.js?.message || 'No se pudo eliminar');
      }
      closeModal($('#btnConfirmDelete'));
      deleteId = null;
    }).catch(()=>alert('Error de red'));
  });

  // ===== Ver
  function ver(id){
    fetch('/productos/'+encodeURIComponent(id), { headers:{ 'Accept':'application/json' }})
      .then(r=>r.json()).then(res=>{
        if(res.ok===false) return alert(res.message||'No se pudo cargar');
        const p = res.producto || res;
        const alerta = (def(p.existencias,0) < def(p.stock_minimo,0)) ? '<div class="tag-alerta" style="margin-top:6px">⚠ Bajo stock</div>' : '';
        $('#ver-body').innerHTML =
          `<ul style="list-style:disc;padding-left:18px;line-height:1.9">
            <li><strong>Código:</strong> ${def(p.codigo)}</li>
            <li><strong>Nombre:</strong> ${def(p.nombre)}</li>
            <li><strong>Unidad:</strong> ${def(p.unidad && p.unidad.descripcion, '-')}</li>
            <li><strong>Categoría:</strong> ${def(p.categoria && p.categoria.nombre, '-')}</li>
            <li><strong>Existencias:</strong> ${def(p.existencias,0)}</li>
            <li><strong>Stock mínimo:</strong> ${def(p.stock_minimo,0)}</li>
            <li><strong>Costo promedio:</strong> ${def(p.costo_promedio,'-')}</li>
            <li><strong>Presentación:</strong> ${def(p.presentacion_detalle)}</li>
          </ul>` + alerta;
        openModal('#ovVer');
      });
  }

  // ===== Editar (cargar/guardar)
  function editar(id){
    fetch('/productos/'+encodeURIComponent(id)+'/edit', { headers:{ 'Accept':'application/json' }})
      .then(r=>r.json()).then(res=>{
        if(res.ok===false) return alert(res.message||'No se pudo cargar');
        const p = res.producto || res;
        $('#edit-id').value = p.id;
        $('#edit-codigo').value = def(p.codigo);
        $('#edit-nombre').value = def(p.nombre);
        $('#edit-unidad').value = def(p.unidad_id, '');
        $('#edit-categoria').value = def(p.categoria_id, '');
        $('#edit-existencias').value = def(p.existencias, 0);
        $('#edit-stock').value = def(p.stock_minimo, 0);
        $('#edit-costo').value = def(p.costo_promedio, '');
        $('#edit-pres').value = def(p.presentacion_detalle, '');
        openModal('#ovEditar');
      });
  }
  const fEditar = $('#form-editar');
  fEditar?.addEventListener('submit', e=>{
    e.preventDefault();
    const id = $('#edit-id').value;
    post('/productos/'+encodeURIComponent(id), new FormData(fEditar)).then(res=>{
      if(!res.ok){
        const msg = res.js?.errors ? Object.values(res.js.errors).flat().join('\n') : (res.js?.message || 'Error al actualizar');
        return alert(msg);
      }
      addOrUpdateRow(res.js.producto);
      closeModal(fEditar);
    }).catch(()=>alert('Error de red'));
  });

  // ===== Crear Unidad
  const fUnidad = $('#form-unidad');
  fUnidad?.addEventListener('submit', e=>{
    e.preventDefault();
    post("{{ route('productos.unidades.inline') }}", new FormData(fUnidad)).then(res=>{
      if(!res.ok){
        const msg = res.js?.errors ? Object.values(res.js.errors).flat().join('\n') : (res.js.message||'No se pudo crear unidad');
        return alert(msg);
      }
      const u = res.js.unidad;
      ['create-unidad','edit-unidad'].forEach(id=>{
        const sel = document.getElementById(id); if(!sel) return;
        if(!sel.querySelector(`option[value="${u.id}"]`)){
          const opt = document.createElement('option'); opt.value = u.id; opt.textContent = u.descripcion; sel.appendChild(opt);
        }
        sel.value = u.id;
      });
      closeModal(fUnidad); fUnidad.reset();
    }).catch(()=>alert('Error de red'));
  });

  // ===== Eliminar Unidad (select)
  function handleDeleteUnidad(selectId){
    const sel = document.getElementById(selectId);
    const val = sel?.value || '';
    if(!val) return alert('Selecciona una unidad.');
    if(!confirm('¿Eliminar esta unidad? Si está en uso por productos, no se podrá borrar.')) return;
    del('/productos/unidades/'+encodeURIComponent(val)).then(res=>{
      if(!res.ok) return alert(res.js?.message || 'No se pudo eliminar unidad');
      // quitar opción en ambos selects
      ['create-unidad','edit-unidad'].forEach(id=>{
        const s = document.getElementById(id);
        s?.querySelector(`option[value="${val}"]`)?.remove();
      });
      alert('Unidad eliminada.');
    }).catch(()=>alert('Error de red'));
  }
  $('#btnBorrarUnidad')?.addEventListener('click', ()=>handleDeleteUnidad('create-unidad'));
  $('#btnBorrarUnidad2')?.addEventListener('click', ()=>handleDeleteUnidad('edit-unidad'));

  // ===== Crear Categoría
  const fCat = $('#form-categoria');
  fCat?.addEventListener('submit', e=>{
    e.preventDefault();
    post("{{ route('productos.categorias.inline') }}", new FormData(fCat)).then(res=>{
      if(!res.ok){
        const msg = res.js?.errors ? Object.values(res.js.errors).flat().join('\n') : (res.js.message||'No se pudo crear categoría');
        return alert(msg);
      }
      const c = res.js.categoria;
      ['create-categoria','edit-categoria'].forEach(id=>{
        const sel = document.getElementById(id); if(!sel) return;
        if(!sel.querySelector(`option[value="${c.id}"]`)){
          const opt = document.createElement('option'); opt.value = c.id; opt.textContent = c.nombre; sel.appendChild(opt);
        }
        sel.value = c.id;
      });
      closeModal(fCat); fCat.reset();
    }).catch(()=>alert('Error de red'));
  });

  // ===== Eliminar Categoría (select)
  function handleDeleteCategoria(selectId){
    const sel = document.getElementById(selectId);
    const val = sel?.value || '';
    if(!val) return alert('Selecciona una categoría.');
    if(!confirm('¿Eliminar esta categoría? Si está en uso por productos, no se podrá borrar.')) return;
    del('/productos/categorias/'+encodeURIComponent(val)).then(res=>{
      if(!res.ok) return alert(res.js?.message || 'No se pudo eliminar categoría');
      ['create-categoria','edit-categoria'].forEach(id=>{
        const s = document.getElementById(id);
        s?.querySelector(`option[value="${val}"]`)?.remove();
      });
      alert('Categoría eliminada.');
    }).catch(()=>alert('Error de red'));
  }
  $('#btnBorrarCategoria')?.addEventListener('click', ()=>handleDeleteCategoria('create-categoria'));
  $('#btnBorrarCategoria2')?.addEventListener('click', ()=>handleDeleteCategoria('edit-categoria'));

  // ===== Agregar a lista (modal)
  const fAddList = $('#form-addlist');
  let currentProd = null;
  function addToList(id){
    currentProd = id;
    $('#addlist-prod').value = id;
    $('#addlist-cant').value = '';
    $('#addlist-prov').value = '';
    $('#addlist-precio').value = '';
    openModal('#ovAddList');
  }
  fAddList?.addEventListener('submit', e=>{
    e.preventDefault();
    const fd = new FormData(fAddList);
    fetch('/listas/quick-add/'+encodeURIComponent(currentProd), {
      method:'POST', headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}, body: fd
    }).then(r=>r.json()).then(res=>{
      if(!res.ok) return alert(res.message || 'No se pudo agregar');
      alert('Producto agregado correctamente a nueva lista (ID '+res.lista_id+').');
      closeModal(fAddList); fAddList.reset();
    }).catch(()=>alert('Error de red'));
  });

  // ===== Actualiza/Inserta fila en tabla
  function addOrUpdateRow(p){
    const tr = document.querySelector(`tr[data-id="${p.id}"]`);
    const alerta = (Number(p.existencias||0) < Number(p.stock_minimo||0));
    const cat = p.categoria ? p.categoria.nombre : (p.categoria_nombre || '-');
    const uni = p.unidad ? p.unidad.descripcion : (p.unidad_descripcion || '-');

    if(tr){
      tr.setAttribute('data-alerta', alerta ? '1':'0');
      tr.querySelector('.codigo').textContent = p.codigo;
      const nombreCell = tr.querySelector('.nombre');
      nombreCell.innerHTML = p.nombre + (alerta ? ' <span class="tag-alerta" title="Bajo stock">⚠ Bajo</span>' : '');
      tr.querySelector('.unidad').textContent = uni || '-';
      tr.querySelector('.categoria').textContent = cat || '-';
      tr.querySelector('.existencias').textContent = p.existencias;
      tr.querySelector('.stock_minimo').textContent = p.stock_minimo;
      return;
    }

    // si no existe, lo insertamos al inicio
    const row = document.createElement('tr');
    row.setAttribute('data-id', p.id);
    row.setAttribute('data-alerta', alerta ? '1':'0');
    row.innerHTML = `
      <td class="codigo" data-label="Código">${p.codigo}</td>
      <td class="nombre" data-label="Nombre">${p.nombre}${alerta?'<span class="tag-alerta" title="Bajo stock">⚠ Bajo</span>':''}</td>
      <td class="unidad" data-label="Unidad">${uni || '-'}</td>
      <td class="categoria" data-label="Categoría">${cat || '-'}</td>
      <td class="existencias" data-label="Existencias">${p.existencias}</td>
      <td class="stock_minimo" data-label="Stock mínimo">${p.stock_minimo}</td>
      <td data-label="Acciones" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
        <button class="pill" data-ver="${p.id}" type="button">Ver</button>
        <a class="pill" href="/kardex/producto/${p.id}">Kardex</a>
        ${isAdmin ? `
          <button class="btn btn-primary btn-xs" data-editar="${p.id}" type="button">Editar</button>
          <button class="btn btn-danger btn-xs" data-eliminar="${p.id}" type="button">Eliminar</button>
          ${alerta ? `<button class="btn btn-ghost btn-xs" data-addlist="${p.id}" type="button">Agregar a lista</button>` : ``}
        `:``}
      </td>
    `;
    const tb = $('#tbody');
    tb?.insertBefore(row, tb.firstChild);
  }
})();
</script>
@endsection
