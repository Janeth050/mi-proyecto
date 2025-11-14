@extends('layouts.app')

@section('title','Movimientos')

@section('content')
@php
  $ES_ADMIN = (isset(auth()->user()->is_admin) && auth()->user()->is_admin)
              || (strtolower(auth()->user()->role ?? auth()->user()->rol ?? '') === 'admin');
  $productos  = $productos  ?? collect();
  $proveedors = $proveedors ?? collect();
@endphp

<style>
  :root{
    --cafe:#8b5e3c;--hover:#70472e;--texto:#5c3a21;--borde:#d9c9b3;
    --ok:#2ecc71;--warn:#f1c40f;--bad:#e74c3c;
  }
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  .toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:12px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none;transition:transform .08s ease}
  .btn:hover{transform:translateY(-1px)}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-gray{background:#6c757d;color:#fff}
  .btn-cancel{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
  .card{background:#fff;border:1px solid var(--borde);border-radius:16px;box-shadow:0 10px 28px rgba(0,0,0,.08);padding:16px}
  .flash{background:#d4edda;color:#155724;border:1px solid #c3e6cb;padding:10px;border-radius:10px;margin-bottom:10px;text-align:center}
  .flash.error{background:#f8d7da;color:#721c24;border-color:#f5c6cb}
  .filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
  .filters select,.filters input{border:1px solid var(--borde);border-radius:12px;padding:8px 10px}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:10px;text-align:center}
  .table th{background:var(--cafe);color:#fff}
  .table tr:nth-child(even){background:#faf6ef}
  .chip{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:700}
  .entrada{border-color:var(--ok);color:var(--ok)}
  .salida{border-color:var(--bad);color:var(--bad)}
  .confirmado{border-color:var(--ok);color:var(--ok)}
  .cancelado{border-color:var(--bad);color:var(--bad);background:#fcebea}
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
  .grid2 input,.grid2 select,.grid2 textarea{
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

<h1 class="page">Movimientos de Inventario</h1>

<div class="toolbar">
  <form action="{{ route('movimientos.index') }}" method="GET" class="filters">
    <select name="tipo">
      <option value="">Tipo (todos)</option>
      <option value="entrada" {{ request('tipo')=='entrada'?'selected':'' }}>Entradas</option>
      <option value="salida"  {{ request('tipo')=='salida'?'selected':''  }}>Salidas</option>
    </select>

    {{-- SOLO Confirmado / Cancelado --}}
    <select name="status">
      <option value="">Estatus (todos)</option>
      <option value="confirmado" {{ request('status')=='confirmado'?'selected':'' }}>Confirmado</option>
      <option value="cancelado"  {{ request('status')=='cancelado'?'selected':''  }}>Cancelado</option>
    </select>

    <input type="text" name="q" placeholder="Buscar producto o usuario" value="{{ request('q') }}">
    <button type="submit" class="btn btn-primary">Filtrar</button>
    <a href="{{ route('movimientos.index') }}" class="btn btn-gray">Limpiar</a>
  </form>

  <button class="btn btn-primary" id="btnNuevo">Nuevo movimiento</button>
</div>

@if(session('success'))
  <div class="flash">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="flash error">{{ session('error') }}</div>
@endif

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>ID</th><th>Producto</th><th>Tipo</th><th>Cantidad</th>
        <th>Existencia después</th><th>Usuario</th><th>Proveedor</th>
        <th>Precio unit.</th><th>Costo total</th>
        <th>Estatus</th><th>Fecha</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($movimientos as $m)
        <tr>
          <td data-label="ID">{{ $m->id }}</td>
          <td data-label="Producto">{{ $m->producto->nombre ?? '—' }}</td>
          <td data-label="Tipo"><span class="chip {{ $m->tipo }}">{{ ucfirst($m->tipo) }}</span></td>
          <td data-label="Cantidad">{{ $m->cantidad }}</td>
          <td data-label="Existencia después">{{ $m->existencias_despues }}</td>
          <td data-label="Usuario">{{ $m->usuario->name ?? '—' }}</td>
          <td data-label="Proveedor">{{ $m->proveedor->nombre ?? '—' }}</td>
          <td data-label="Precio unit.">
            @if($m->costo_unitario) ${{ number_format($m->costo_unitario,2) }} @else — @endif
          </td>
          <td data-label="Costo total">
            @if($m->costo_total) ${{ number_format($m->costo_total,2) }} @else — @endif
          </td>
          <td data-label="Estatus"><span class="chip {{ $m->status }}">{{ ucfirst($m->status) }}</span></td>
          <td data-label="Fecha">{{ $m->created_at->format('d/m/Y H:i') }}</td>
          <td data-label="Acciones" style="display:flex;justify-content:center;gap:8px;flex-wrap:wrap">
            @if($ES_ADMIN && $m->status !== 'cancelado')
              <form action="{{ route('movimientos.cancelar', $m->id) }}" method="POST"
                    onsubmit="return confirm('¿Cancelar este movimiento? Esto revertirá el stock.')">
                @csrf
                <button type="submit" class="btn btn-cancel">Cancelar</button>
              </form>
            @else
              <span style="color:#999;">—</span>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="12" style="text-align:center;color:#7a6b5f">No hay movimientos registrados.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@if(method_exists($movimientos,'links'))
  <div style="margin-top:12px">
    {{ $movimientos->links() }}
  </div>
@endif

{{-- ================= Modal: Nuevo movimiento ================= --}}
<div class="overlay" id="ovNuevo">
  <div class="modal-card">
    <div class="modal-head">
      <h3>Nuevo movimiento</h3>
      <button class="close-x" data-close>&times;</button>
    </div>
    <form id="form-nuevo" class="grid2">
      @csrf
      <label style="grid-column:1/-1">Producto
        <select name="producto_id" id="mov-producto" required>
          <option value="">— Selecciona —</option>
          @foreach($productos as $p)
            <option value="{{ $p->id }}">{{ $p->codigo }} — {{ $p->nombre }} (Stock: {{ $p->existencias }})</option>
          @endforeach
        </select>
      </label>

      <label>Tipo
        <select name="tipo" id="mov-tipo" required {{ $ES_ADMIN ? '' : 'disabled' }}>
          <option value="salida" selected>Salida</option>
          @if($ES_ADMIN)
            <option value="entrada">Entrada</option>
          @endif
        </select>
        @if(!$ES_ADMIN)
          <input type="hidden" name="tipo" value="salida">
        @endif
      </label>

      <label>Cantidad
        <input type="number" name="cantidad" id="mov-cantidad" min="1" value="1" required>
      </label>

      <label>Precio por unidad (opcional)
        <input type="number" step="0.0001" min="0" name="costo_unitario" id="mov-precio" placeholder="0.00">
      </label>

      <label>Proveedor (opcional)
        <select name="proveedor_id" id="mov-proveedor">
          <option value="">— Sin proveedor —</option>
          @foreach($proveedors as $pr)
            <option value="{{ $pr->id }}">{{ $pr->nombre }}</option>
          @endforeach
        </select>
      </label>

      <label>Costo total (auto)
        <input type="text" id="mov-total" readonly placeholder="—">
      </label>

      <label style="grid-column:1/-1">Descripción (opcional)
        <textarea name="descripcion" rows="3" placeholder="Notas del movimiento"></textarea>
      </label>

      <div style="grid-column:1/-1;display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
        <button type="button" class="btn btn-gray" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  const $ = s => document.querySelector(s);
  const $$ = s => Array.from(document.querySelectorAll(s));
  const token = '{{ csrf_token() }}';
  const isAdmin = {{ $ES_ADMIN ? 'true' : 'false' }};

  // Modal
  function open(){ $('#ovNuevo')?.classList.add('show'); }
  function close(){ $('#ovNuevo')?.classList.remove('show'); }
  $('#btnNuevo')?.addEventListener('click', open);
  $$('#ovNuevo [data-close]').forEach(b => b.addEventListener('click', close));
  window.addEventListener('keydown', e => { if(e.key==='Escape') close(); });

  // UI proveedor según tipo
  const tipoSel = $('#mov-tipo');
  const provSel = $('#mov-proveedor');
  function toggleProveedor(){
    const t = (tipoSel?.value || 'salida');
    provSel?.closest('label').classList.toggle('muted', t !== 'entrada');
  }
  tipoSel?.addEventListener('change', toggleProveedor);
  toggleProveedor();

  // Cálculo de total
  const cant = $('#mov-cantidad');
  const precio = $('#mov-precio');
  const total = $('#mov-total');
  function calcTotal(){
    const c = parseFloat(cant.value || '0');
    const p = parseFloat(precio.value || '0');
    if(!isFinite(c) || !isFinite(p) || c <= 0 || p < 0) { total.value = '—'; return; }
    total.value = (c*p).toFixed(2);
  }
  cant?.addEventListener('input', calcTotal);
  precio?.addEventListener('input', calcTotal);
  calcTotal();

  // Envío
  $('#form-nuevo')?.addEventListener('submit', function(e){
    e.preventDefault();
    const fd = new FormData(this);
    if(!isAdmin) { fd.set('tipo','salida'); }

    fetch(`{{ route('movimientos.store') }}`, {
      method:'POST',
      headers:{ 'X-CSRF-TOKEN': token, 'Accept':'application/json' },
      body: fd
    }).then(async r=>{
      if(r.ok){ location.reload(); }
      else{
        const js = await r.json().catch(()=>null);
        alert(js?.message || 'No se pudo guardar el movimiento.');
      }
    }).catch(()=>alert('Error de red'));
  });
})();
</script>
@endsection
