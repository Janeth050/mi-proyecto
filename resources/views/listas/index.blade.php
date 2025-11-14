@extends('layouts.app')
@section('title','Listas de pedido')

@section('content')
@php
  $ES_ADMIN = (isset(auth()->user()->is_admin) && auth()->user()->is_admin)
              || (strtolower(auth()->user()->role ?? auth()->user()->rol ?? '') === 'admin');
@endphp

<style>
  :root{ --cafe:#8b5e3c; --borde:#d9c9b3; --ok:#2ecc71; --bad:#e74c3c; --warn:#f1c40f; }
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  .card{background:#fff;border:1px solid var(--borde);border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,.06);padding:16px;margin-bottom:16px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:12px;padding:10px 14px;font-weight:700;cursor:pointer}
  .btn-primary{background:var(--cafe);color:#fff}
  .btn-ok{background:var(--ok);color:#fff}
  .btn-gray{background:#6c757d;color:#fff}
  .btn-danger{background:var(--bad);color:#fff}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:10px;text-align:center}
  .table th{background:#8b5e3c;color:#fff}
  .tag{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:700}
  .borrador{border-color:#999;color:#666}
  .cerrada{border-color:var(--ok);color:var(--ok)}
  .muted{color:#7a6b5f}
</style>

<h1 class="page">Listas de pedido</h1>

{{-- ===== Lista ACTIVA ===== --}}
<div class="card">
  <h3 style="margin:0 0 10px">Lista activa</h3>

  @if($activa)
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:10px">
      <span class="tag borrador">Borrador</span>
      <span class="muted">ID: {{ $activa->id }}</span>
      <span class="muted">Creador: {{ $activa->creador->name ?? '-' }}</span>
      <span class="muted">Total est.: ${{ number_format($activa->total_estimado,2) }}</span>

      <div style="margin-left:auto;display:flex;gap:8px">
        <form action="{{ route('listas.archivar',$activa) }}" method="POST" onsubmit="return confirm('¿Archivar esta lista?');">
          @csrf
          <button class="btn btn-ok" type="submit">Archivar</button>
        </form>
        <a href="{{ route('listas.export',$activa) }}" class="btn btn-gray">Exportar CSV</a>
        <form action="{{ route('listas.destroy',$activa) }}" method="POST" onsubmit="return confirm('¿Eliminar la lista activa?');">
          @csrf @method('DELETE')
          <button class="btn btn-danger" type="submit">Eliminar</button>
        </form>
      </div>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>#</th><th>Producto</th><th>Cantidad</th><th>Proveedor</th><th>Precio est.</th><th>Importe</th>
        </tr>
      </thead>
      <tbody>
        @foreach($activa->items as $it)
          @php $imp = $it->precio_estimado !== null ? $it->precio_estimado * $it->cantidad : null; @endphp
          <tr>
            <td>{{ $it->id }}</td>
            <td>{{ $it->producto->nombre ?? '-' }}</td>
            <td>{{ $it->cantidad }}</td>
            <td>{{ $it->proveedor->nombre ?? '—' }}</td>
            <td>{{ $it->precio_estimado !== null ? ('$'.number_format($it->precio_estimado,2)) : '—' }}</td>
            <td>{{ $imp !== null ? ('$'.number_format($imp,2)) : '—' }}</td>
          </tr>
        @endforeach
        @if($activa->items->count() === 0)
          <tr><td colspan="6" class="muted">Vacía. Agrega desde <strong>Productos → Agregar a lista</strong>.</td></tr>
        @endif
      </tbody>
    </table>
  @else
    <p class="muted" style="margin:0">No tienes lista activa. Agrega un producto desde <strong>Productos</strong> para crearla.</p>
  @endif
</div>

{{-- ===== Histórico ===== --}}
<div class="card">
  <h3 style="margin:0 0 10px">Histórico</h3>
  <table class="table">
    <thead>
      <tr>
        <th>ID</th><th># Ítems</th><th>Total est.</th><th>Creada</th><th>Estatus</th><th>Exportar</th>
      </tr>
    </thead>
    <tbody>
      @forelse($historico as $l)
        <tr>
          <td>{{ $l->id }}</td>
          <td>{{ $l->items_count }}</td>
          <td>${{ number_format($l->total_estimado,2) }}</td>
          <td>{{ $l->created_at->format('d/m/Y H:i') }}</td>
          <td><span class="tag cerrada">Cerrada</span></td>
          <td><a class="btn btn-gray" href="{{ route('listas.export',$l) }}">CSV</a></td>
        </tr>
      @empty
        <tr><td colspan="6" class="muted">Aún no tienes listas archivadas.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection

