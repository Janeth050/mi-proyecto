@extends('layouts.app')

@section('title','Proveedores')

@section('content')
<style>
  :root{--cafe:#8b5e3c;--hover:#70472e;--texto:#5c3a21;--borde:#d9c9b3}
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}

  .toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-back{background:#fff;border:1px solid var(--borde);color:#70472e}.btn-back:hover{background:#f2e8db}
  .btn-danger{background:#e74c3c;color:#fff}.btn-danger:hover{background:#c0392b}
  .pill{padding:8px 12px;border-radius:10px;border:1px solid var(--borde);background:#fff;color:#70472e;font-weight:700;text-decoration:none}
  .pill:hover{background:#f2e8db}

  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px}
  .flash{background:#d4edda;color:#155724;border:1px solid #c3e6cb;padding:10px;border-radius:10px;margin-bottom:10px}

  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:10px;text-align:center}
  .table th{background:#8b5e3c;color:#fff}
  .table tr:nth-child(even){background:#faf6ef}

  @media(max-width:760px){
    .table thead{display:none}
    .table tr{display:block;border:1px solid var(--borde);margin-bottom:10px;border-radius:10px;overflow:hidden}
    .table td{display:flex;justify-content:space-between;gap:12px;border:none;border-bottom:1px solid #eee}
    .table td:last-child{border-bottom:none}
    .table td::before{content:attr(data-label);font-weight:700;color:#7a6b5f}
  }
</style>

<h1 class="page">Proveedores</h1>

<div class="toolbar">
  <a class="btn btn-primary" href="{{ route('proveedors.create') }}"> Nuevo proveedor</a>
</div>

@if(session('success')) <div class="flash">{{ session('success') }}</div> @endif
@if(session('error'))   <div class="flash" style="background:#f8d7da;color:#721c24;border-color:#f5c6cb">{{ session('error') }}</div> @endif

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
        <th style="min-width:220px">Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($proveedors as $prov)
      <tr>
        <td data-label="#"> {{ $prov->id }}</td>
        <td data-label="Nombre">{{ $prov->nombre }}</td>
        <td data-label="Teléfono">{{ $prov->telefono ?? '—' }}</td>
        <td data-label="Correo">{{ $prov->correo ?? '—' }}</td>
        <td data-label="Dirección">{{ $prov->direccion ?? '—' }}</td>
        <td data-label="Notas">{{ $prov->notas ?? '—' }}</td>
        <td data-label="Acciones" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
          <a class="pill" href="{{ route('proveedors.edit',$prov->id) }}">Editar</a>
          <form action="{{ route('proveedors.destroy',$prov->id) }}" method="POST"
                onsubmit="return confirm('¿Eliminar proveedor {{ $prov->nombre }}?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">Eliminar</button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="7" style="text-align:center;color:#7a6b5f">No hay proveedores registrados.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@if(method_exists($proveedors,'links'))
  <div style="margin-top:12px">
    {{ $proveedors->links() }}
  </div>
@endif
@endsection
