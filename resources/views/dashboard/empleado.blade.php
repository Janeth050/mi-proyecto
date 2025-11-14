@extends('layouts.app')

@section('title','Dashboard')

@section('content')
<style>
  :root{--cafe:#8b5e3c;--texto:#5c3a21;--borde:#d9c9b3;--ok:#2ecc71;--bad:#e74c3c}
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:8px;text-align:center}
  .table th{background:var(--cafe);color:#fff}
  .table tr:nth-child(even){background:#faf6ef}
  .chip{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:600}
  .chip.entrada{border-color:var(--ok);color:var(--ok)}
  .chip.salida{border-color:var(--bad);color:var(--bad)}
  .table-wrap{width:100%;overflow-x:auto}
  @media (max-width: 760px){
    .table thead{display:none}
    .table tr{display:block;border:1px solid var(--borde);margin-bottom:8px;border-radius:10px;overflow:hidden}
    .table td{display:flex;justify-content:space-between;gap:10px;border:none;border-bottom:1px solid #eee}
    .table td:last-child{border-bottom:none}
    .table td::before{content:attr(data-label);font-weight:700;color:#7a6b5f}
  }
</style>

@php $tz = config('app.timezone', 'America/Monterrey'); @endphp

<h1 class="page">Mi actividad</h1>

<div class="card">
  <h3 style="margin:0 0 10px;color:#5c3a21">Mis últimos movimientos</h3>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Fecha</th><th>Tipo</th><th>Producto</th><th>Cantidad</th></tr></thead>
      <tbody>
        @forelse($ultimos as $m)
          <tr>
            <td data-label="Fecha">{{ $m->created_at->timezone($tz)->format('d/m/Y H:i') }}</td>
            <td data-label="Tipo"><span class="chip {{ $m->tipo }}">{{ ucfirst($m->tipo) }}</span></td>
            <td data-label="Producto">{{ $m->producto->nombre ?? '-' }}</td>
            <td data-label="Cantidad">{{ $m->cantidad }}</td>
          </tr>
        @empty
          <tr><td colspan="4" style="text-align:center">Aún no registras movimientos.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
