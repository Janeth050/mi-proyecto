@extends('layouts.app')

@section('title','Kardex de '.$producto->nombre)

@section('content')
<style>
  :root{--cafe:#8b5e3c;--hover:#70472e;--texto:#5c3a21;--borde:#d9c9b3;--ok:#2ecc71;--bad:#e74c3c}
  h1.page{color:var(--cafe);margin:0 0 6px;font-size:28px;font-weight:800}
  .muted{color:#7a6b5f;margin-bottom:10px}
  .toolbar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;align-items:center}
  .filters{display:flex;gap:8px;flex-wrap:wrap}
  .filters input{border:1px solid var(--borde);border-radius:10px;padding:8px 10px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-gray{background:#6c757d;color:#fff}.btn-gray:hover{background:#5a6268}
  .btn-back{background:#fff;border:1px solid var(--borde);color:#70472e}.btn-back:hover{background:#f2e8db}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:10px;text-align:center}
  .table th{background:var(--cafe);color:#fff}
  .table tr:nth-child(even){background:#faf6ef}
  .chip{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:600}
  .entrada{border-color:var(--ok);color:var(--ok)}
  .salida{border-color:var(--bad);color:var(--bad)}
  @media(max-width:760px){
    .table thead{display:none}
    .table tr{display:block;border:1px solid var(--borde);margin-bottom:10px;border-radius:10px;overflow:hidden}
    .table td{display:flex;justify-content:space-between;gap:12px;border:none;border-bottom:1px solid #eee}
    .table td:last-child{border-bottom:none}
    .table td::before{content:attr(attr-label);font-weight:700;color:#7a6b5f}
  }
</style>

<h1 class="page">Kardex de: {{ $producto->nombre }}</h1>
<div class="muted">
  Código: {{ $producto->codigo }} · Unidad: {{ $producto->unidad->descripcion ?? '-' }} ·
  Existencias actuales: <strong>{{ $producto->existencias }}</strong>
</div>

<div class="toolbar">
  <form class="filters" action="{{ route('kardex.producto',$producto->id) }}" method="GET">
  </form>
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Movimiento</th>
        <th>Tipo</th>
        <th>Entrada</th>
        <th>Salida</th>
        <th>Saldo</th>
        <th>Costo unit.</th>
        <th>Costo total</th>
      </tr>
    </thead>
    <tbody>
      @forelse($kardex as $k)
        <tr>
          <td attr-label="Fecha">{{ $k->fecha?->format('d/m/Y H:i') }}</td>
          <td attr-label="Movimiento" class="muted">
            @if($k->movimiento) #{{ $k->movimiento->id }} @else — @endif
          </td>
          <td attr-label="Tipo">
            <span class="chip {{ $k->tipo }}">{{ ucfirst($k->tipo) }}</span>
          </td>
          <td attr-label="Entrada">{{ $k->entrada }}</td>
          <td attr-label="Salida">{{ $k->salida }}</td>
          <td attr-label="Saldo"><strong>{{ $k->saldo }}</strong></td>
          <td attr-label="Costo unit.">
            @if(!is_null($k->costo_unitario)) ${{ number_format($k->costo_unitario,4) }} @else — @endif
          </td>
          <td attr-label="Costo total">
            @if(!is_null($k->costo_total)) ${{ number_format($k->costo_total,4) }} @else — @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="8" style="text-align:center;color:#7a6b5f">No hay registros del período seleccionado.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
