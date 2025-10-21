@extends('layouts.app')

@section('title','Movimientos')

@section('content')
<style>
  :root{
    --cafe:#8b5e3c;--hover:#70472e;--texto:#5c3a21;--borde:#d9c9b3;
    --ok:#2ecc71;--warn:#f1c40f;--bad:#e74c3c;
  }
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  .toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-back{background:#fff;border:1px solid var(--borde);color:#70472e}.btn-back:hover{background:#f2e8db}
  .btn-gray{background:#6c757d;color:#fff}.btn-gray:hover{background:#5a6268}
  .btn-cancel{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
  .btn-cancel:hover{background:#f1b0b7}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px}
  .flash{background:#d4edda;color:#155724;border:1px solid #c3e6cb;padding:10px;border-radius:10px;margin-bottom:10px;text-align:center}
  .filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
  .filters select,.filters input{border:1px solid var(--borde);border-radius:10px;padding:8px 10px}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:10px;text-align:center}
  .table th{background:var(--cafe);color:#fff}
  .table tr:nth-child(even){background:#faf6ef}
  .chip{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:600}
  .entrada{border-color:var(--ok);color:var(--ok)}
  .salida{border-color:var(--bad);color:var(--bad)}
  .pendiente{border-color:var(--warn);color:#9a7d0a}
  .confirmado{border-color:var(--ok);color:var(--ok)}
  .cancelado{border-color:var(--bad);color:var(--bad);background:#fcebea}
  @media(max-width:760px){
    .table thead{display:none}
    .table tr{display:block;border:1px solid var(--borde);margin-bottom:10px;border-radius:10px;overflow:hidden}
    .table td{display:flex;justify-content:space-between;gap:12px;border:none;border-bottom:1px solid #eee}
    .table td:last-child{border-bottom:none}
    .table td::before{content:attr(data-label);font-weight:700;color:#7a6b5f}
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

    <select name="status">
      <option value="">Estatus (todos)</option>
      <option value="pendiente"  {{ request('status')=='pendiente'?'selected':'' }}>Pendiente</option>
      <option value="confirmado" {{ request('status')=='confirmado'?'selected':'' }}>Confirmado</option>
      <option value="cancelado"  {{ request('status')=='cancelado'?'selected':'' }}>Cancelado</option>
    </select>

    <input type="text" name="q" placeholder="Buscar producto o usuario" value="{{ request('q') }}">
    <button type="submit" class="btn btn-primary"> Filtrar</button>
    <a href="{{ route('movimientos.index') }}" class="btn btn-gray">Limpiar</a>
  </form>

  <div style="display:flex;gap:8px;">
    <a class="btn btn-primary" href="{{ route('movimientos.create') }}">Nuevo movimiento</a>
  </div>
</div>

@if(session('success'))
  <div class="flash">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="flash" style="background:#f8d7da;color:#721c24;border-color:#f5c6cb">{{ session('error') }}</div>
@endif

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>ID</th><th>Producto</th><th>Tipo</th><th>Cantidad</th>
        <th>Existencia después</th><th>Usuario</th><th>Proveedor</th>
        <th>Costo total</th><th>Estatus</th><th>Fecha</th><th>Acciones</th>
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
          <td data-label="Costo total">
            @if($m->costo_total)
              ${{ number_format($m->costo_total,2) }}
            @else
              —
            @endif
          </td>
          <td data-label="Estatus">
            <span class="chip {{ $m->status }}">{{ ucfirst($m->status) }}</span>
          </td>
          <td data-label="Fecha">{{ $m->created_at->format('d/m/Y H:i') }}</td>

          <td data-label="Acciones" style="display:flex;justify-content:center;gap:8px;flex-wrap:wrap">
            @if($m->status !== 'cancelado')
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
        <tr><td colspan="11" style="text-align:center;color:#7a6b5f">No hay movimientos registrados.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@if(method_exists($movimientos,'links'))
  <div style="margin-top:12px">
    {{ $movimientos->links() }}
  </div>
@endif
@endsection

