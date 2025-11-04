@extends('layouts.app')

@section('title','Listas de pedido')

@section('content')
@php
  $ES_ADMIN = (isset(auth()->user()->is_admin) && auth()->user()->is_admin)
              || (strtolower(auth()->user()->role ?? auth()->user()->rol ?? '') === 'admin');
@endphp

<style>
  :root{
    --cafe:#8b5e3c; --hover:#70472e; --texto:#5c3a21; --borde:#d9c9b3;
    --ok:#2ecc71; --warn:#f1c40f; --bad:#e74c3c; --bg:#fffdf8;
  }
  body{background:var(--bg)}
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}

  .toolbar{display:flex;justify-content:flex-end;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:12px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none;transition:transform .06s ease}
  .btn:hover{transform:translateY(-1px)}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-gray{background:#6c757d;color:#fff}.btn-gray:hover{filter:brightness(.95)}
  .btn-danger{background:var(--bad);color:#fff}.btn-danger:hover{filter:brightness(.92)}
  .btn-warn{background:var(--warn);color:#333}.btn-warn:hover{filter:brightness(.95)}
  .btn-ok{background:var(--ok);color:#fff}.btn-ok:hover{filter:brightness(.95)}
  .btn-cancel{background:#e67e22;color:#fff}.btn-cancel:hover{filter:brightness(.95)}
  .btn-xs{padding:6px 10px;border-radius:10px;font-size:12px}

  .card{background:#fff;border:1px solid var(--borde);border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,.06);padding:16px}
  .line{height:1px;background:var(--borde);margin:10px 0}

  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:10px;text-align:center}
  .table th{background:#8b5e3c;color:#fff}
  .table tr:nth-child(even){background:#faf6ef}

  .chip{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:700}
  .borrador{border-color:#999;color:#666}
  .enviada{border-color:var(--warn);color:#9a7d0a}
  .cerrada{border-color:var(--ok);color:var(--ok)}
  .cancelada{border-color:var(--bad);color:var(--bad)}
  .muted{color:#7a6b5f}

  /* ===== Modal ===== */
  .overlay{position:fixed;inset:0;background:rgba(0,0,0,.28);z-index:9999;display:none;align-items:center;justify-content:center;padding:14px}
  .overlay.show{display:flex}
  .modal-card{
    width:min(980px,96vw);
    background:#fff;
    border:1px solid var(--borde);
    border-radius:18px;
    box-shadow:0 22px 60px rgba(0,0,0,.18);
    overflow:hidden;
  }
  .modal-head{background:#fff8f0;border-bottom:1px solid var(--borde);padding:14px 18px;display:flex;justify-content:space-between;align-items:center}
  .modal-head h3{margin:0;color:var(--texto)}
  .close-x{background:transparent;border:none;font-size:22px;line-height:16px;cursor:pointer;color:#777}
  .modal-body{padding:16px}

  .meta{display:flex;gap:12px;flex-wrap:wrap;color:#6b5f55;font-weight:600}
  .meta .tag{background:#f4eadf;border:1px solid var(--borde);padding:4px 10px;border-radius:999px}

  @media(max-width:760px){
    .table thead{display:none}
    .table tr{display:block;border:1px solid var(--borde);margin-bottom:10px;border-radius:12px;overflow:hidden}
    .table td{display:flex;justify-content:space-between;gap:12px;border:none;border-bottom:1px solid #eee}
    .table td:last-child{border-bottom:none}
    .table td::before{content:attr(data-label);font-weight:700;color:#7a6b5f}
  }
</style>

<h1 class="page">Listas de pedido</h1>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Comentario</th>
        <th>Estatus</th>
        <th># Ítems</th>
        <th>Total est.</th>
        <th>Creada</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody id="tbody">
      @foreach(\App\Models\ListaPedido::with('creador')->withCount('items')->latest()->get() as $l)
        <tr data-id="{{ $l->id }}">
          <td data-label="ID">{{ $l->id }}</td>
          <td data-label="Usuario">{{ $l->creador->name ?? '-' }}</td>
          <td data-label="Comentario">{{ $l->comentario ?? '—' }}</td>
          <td data-label="Estatus"><span class="chip {{ $l->status }}">{{ ucfirst($l->status) }}</span></td>
          <td data-label="# Ítems">{{ $l->items_count }}</td>
          <td data-label="Total est.">${{ number_format($l->total_estimado,2) }}</td>
          <td data-label="Creada">{{ $l->created_at->format('d/m/Y H:i') }}</td>
          <td data-label="Acciones" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
            <button class="btn btn-primary btn-xs" data-ver="{{ $l->id }}">Ver</button>
            @if($l->status==='borrador')
              <button class="btn btn-danger btn-xs" data-eliminar="{{ $l->id }}">Eliminar</button>
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

{{-- ===== MODAL: Ver (solo lectura) ===== --}}
<div class="overlay" id="ovVer">
  <div class="modal-card">
    <div class="modal-head">
      <h3 id="v-title">Lista</h3>
      <button class="close-x" data-close>&times;</button>
    </div>
    <div class="modal-body">
      <div id="v-meta" class="meta"></div>

      <div class="line"></div>

      <div id="v-status-bar" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px"></div>

      <div class="card" style="padding:10px">
        <h4 style="margin:6px 0">Materiales</h4>
        <table class="table">
          <thead>
            <tr>
              <th>#</th><th>Producto</th><th>Cantidad</th><th>Proveedor</th><th>Precio est.</th><th>Importe</th>
            </tr>
          </thead>
          <tbody id="v-items"></tbody>
        </table>
      </div>

      <div class="muted" style="margin-top:8px">
        * La edición de materiales se realiza desde <strong>Productos → Agregar a lista</strong>.
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const $  = s => document.querySelector(s);
  const $$ = s => Array.from(document.querySelectorAll(s));
  const token = '{{ csrf_token() }}';

  // ===== helpers
  function open(id){ $(id).classList.add('show'); }
  function close(btn){ btn.closest('.overlay')?.classList.remove('show'); }
  $$('.overlay [data-close]').forEach(b => b.addEventListener('click', e => close(e.target)));
  window.addEventListener('keydown', e => { if(e.key==='Escape'){ $$('.overlay.show').at(-1)?.classList.remove('show'); }});
  const money = n => '$'+Number(n||0).toFixed(2);

  // ===== Tabla principal
  $('#tbody').addEventListener('click', e=>{
    const btn = e.target.closest('button'); if(!btn) return;
    const tr  = e.target.closest('tr[data-id]'); if(!tr) return;
    const id  = tr.getAttribute('data-id');
    if(btn.hasAttribute('data-ver'))      return cargarYVer(id);
    if(btn.hasAttribute('data-eliminar')) return eliminar(id);
  });

  function eliminar(id){
    if(!confirm('¿Eliminar esta lista en borrador?')) return;
    fetch(`/listas/${id}`, { method:'DELETE', headers:{ 'X-CSRF-TOKEN': token, 'Accept':'application/json' }})
      .then(r=>r.json()).then(res=>{
        if(res.ok){ document.querySelector(`tr[data-id="${id}"]`)?.remove(); }
        else alert(res.message || 'No se pudo eliminar');
      }).catch(()=>alert('Error de red'));
  }

  // ===== Ver lista (solo lectura)
  function cargarYVer(id){
    fetch(`/listas/${id}`, { headers:{ 'Accept':'application/json' }})
      .then(r=>r.json()).then(res=>{
        if(!res.ok) return alert(res.message || 'No se pudo cargar');
        renderLista(res.lista, res.total_estimado ?? 0);
        open('#ovVer');
      }).catch(()=>alert('Error de red'));
  }

  function renderLista(L, total){
    $('#v-title').textContent = `Lista #${L.id}`;
    $('#v-meta').innerHTML = `
      <span class="tag">Creador: ${L.creador?.name ?? '-'}</span>
      <span class="tag">Creada: ${new Date(L.created_at).toLocaleString()}</span>
      <span class="tag">Estatus: <strong>${(L.status||'').toUpperCase()}</strong></span>
      <span class="tag">Total: ${money(total)}</span>
    `;

    // Botones de estatus (solo cambiar estado; NADA de edición de ítems aquí)
    const bar = $('#v-status-bar'); bar.innerHTML = '';
    if(L.status==='borrador'){
      bar.innerHTML = `
        <button class="btn btn-warn"  id="btnEnviar">Enviar</button>
        <button class="btn btn-cancel" id="btnCancelar">Cancelar</button>
        <button class="btn btn-danger" id="btnEliminar">Eliminar</button>
      `;
    }else if(L.status==='enviada'){
      bar.innerHTML = `
        <button class="btn btn-ok"    id="btnCerrar">Cerrar</button>
        <button class="btn btn-cancel" id="btnCancelar">Cancelar</button>
      `;
    }

    // Bind acciones de estado
    $('#btnEnviar')?.addEventListener('click', ()=>accion(`/listas/${L.id}/enviar`));
    $('#btnCerrar')?.addEventListener('click', ()=>accion(`/listas/${L.id}/cerrar`));
    $('#btnCancelar')?.addEventListener('click', ()=>accion(`/listas/${L.id}/cancelar`));
    $('#btnEliminar')?.addEventListener('click', ()=>{
      if(!confirm('¿Eliminar lista en borrador?')) return;
      fetch(`/listas/${L.id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'} })
        .then(r=>r.json()).then(res=>{
          if(res.ok){ document.querySelector(`tr[data-id="${L.id}"]`)?.remove(); close($('#ovVer')); }
          else alert(res.message || 'No se pudo eliminar');
        });
    });

    function accion(url){
      fetch(url, { method:'POST', headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'} })
        .then(r=>r.json()).then(res=>{
          if(!res.ok) return alert(res.message || 'Acción no realizada');
          refreshRow(L.id);
          close($('#ovVer'));
        }).catch(()=>alert('Error de red'));
    }

    // Render items (solo lectura)
    const tbody = $('#v-items'); tbody.innerHTML = '';
    (L.items ?? []).forEach(it => {
      const tr = document.createElement('tr');
      const importe = (it.precio_estimado!=null) ? (Number(it.precio_estimado)*Number(it.cantidad)) : null;
      tr.innerHTML = `
        <td>${it.id}</td>
        <td>${it.producto?.nombre ?? '-'}</td>
        <td>${it.cantidad}</td>
        <td>${it.proveedor?.nombre ?? '—'}</td>
        <td>${it.precio_estimado!=null ? money(it.precio_estimado) : '—'}</td>
        <td>${importe!=null ? money(importe) : '—'}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  // Refrescar fila en la tabla principal
  function refreshRow(id){
    fetch(`/listas/${id}`, { headers:{'Accept':'application/json'} })
      .then(r=>r.json()).then(res=>{
        if(!res.ok) return;
        const tr = document.querySelector(`tr[data-id="${id}"]`);
        if(!tr) return;
        const l = res.lista;
        tr.innerHTML = `
          <td>${l.id}</td>
          <td>${l.creador?.name ?? '-'}</td>
          <td>${l.comentario ?? '—'}</td>
          <td><span class="chip ${l.status}">${(l.status||'').charAt(0).toUpperCase() + (l.status||'').slice(1)}</span></td>
          <td>${l.items?.length ?? 0}</td>
          <td>${money(res.total_estimado ?? 0)}</td>
          <td>${new Date(l.created_at).toLocaleString()}</td>
          <td style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
            <button class="btn btn-primary btn-xs" data-ver="${l.id}">Ver</button>
            ${l.status==='borrador' ? `<button class="btn btn-danger btn-xs" data-eliminar="${l.id}">Eliminar</button>` : ``}
          </td>
        `;
      });
  }
})();
</script>
@endsection
