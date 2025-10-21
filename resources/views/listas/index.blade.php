@extends('layouts.app')

@section('title','Listas de pedido')

@section('content')
<style>
  :root{--cafe:#8b5e3c;--hover:#70472e;--texto:#5c3a21;--borde:#d9c9b3;--ok:#2ecc71;--warn:#f1c40f;--bad:#e74c3c}
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  .toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-gray{background:#6c757d;color:#fff}.btn-gray:hover{background:#5a6268}
  .btn-danger{background:#e74c3c;color:#fff}.btn-danger:hover{background:#c0392b}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px}
  .flash{background:#d4edda;color:#155724;border:1px solid #c3e6cb;padding:10px;border-radius:10px;margin-bottom:10px;text-align:center}
  .flash.error{background:#f8d7da;color:#721c24;border-color:#f5c6cb}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:10px;text-align:center}
  .table th{background:var(--cafe);color:#fff}
  .table tr:nth-child(even){background:#faf6ef}
  .chip{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:700}
  .borrador{border-color:#999;color:#666}
  .enviada{border-color:var(--warn);color:#9a7d0a}
  .cerrada{border-color:var(--ok);color:var(--ok)}
  .cancelada{border-color:var(--bad);color:var(--bad)}
  @media(max-width:820px){
    .table thead{display:none}
    .table tr{display:block;border:1px solid var(--borde);margin-bottom:10px;border-radius:10px;overflow:hidden}
    .table td{display:flex;justify-content:space-between;gap:12px;border:none;border-bottom:1px solid #eee}
    .table td:last-child{border-bottom:none}
    .table td::before{content:attr(data-label);font-weight:700;color:#7a6b5f}
  }
</style>

<h1 class="page">Listas de pedido</h1>

@if(session('success'))<div class="flash">{{ session('success') }}</div>@endif
@if(session('error'))  <div class="flash error">{{ session('error') }}</div>@endif

<div class="toolbar">
  <div></div>
  <a class="btn btn-primary" href="{{ route('listas.create') }}">Nueva lista</a>
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Comentario</th>
        <th>Estatus</th>
        <th># Ítems</th>
        <th>Total estimado</th>
        <th>Creada</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($listas as $l)
        <tr>
          <td data-label="ID">{{ $l->id }}</td>
          <td data-label="Usuario">{{ $l->creador->name ?? '-' }}</td>
          <td data-label="Comentario">{{ $l->comentario ?? '—' }}</td>
          <td data-label="Estatus"><span class="chip {{ $l->status }}">{{ ucfirst($l->status) }}</span></td>
          <td data-label="# Ítems">{{ $l->items()->count() }}</td>
          <td data-label="Total estimado">${{ number_format($l->total_estimado,2) }}</td>
          <td data-label="Creada">{{ $l->created_at->format('d/m/Y H:i') }}</td>
          <td data-label="Acciones" style="display:flex;justify-content:center;gap:8px;flex-wrap:wrap">
            <a class="btn btn-primary" href="{{ route('listas.show', $l->id) }}">Ver</a>
            @if($l->status==='borrador')
              <form action="{{ route('listas.destroy',$l->id) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar esta lista en borrador?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger" type="submit">Eliminar</button>
              </form>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="8" style="text-align:center;color:#7a6b5f">No hay listas aún.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
