@extends('layouts.app')

@section('title','Lista #'.$lista->id)

@section('content')
<style>
  :root{--cafe:#8b5e3c;--hover:#70472e;--borde:#d9c9b3;--ok:#2ecc71;--warn:#f1c40f;--bad:#e74c3c}
  h1.page{color:var(--cafe);margin:0 0 6px;font-size:28px;font-weight:800}
  .muted{color:#7a6b5f;margin-bottom:10px}
  .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none}
  .btn-primary{background:var(--cafe);color:#fff}.btn-primary:hover{background:var(--hover)}
  .btn-warn{background:#f1c40f;color:#333}.btn-warn:hover{filter:brightness(.95)}
  .btn-ok{background:#2ecc71;color:#fff}.btn-ok:hover{filter:brightness(.95)}
  .btn-cancel{background:#e67e22;color:#fff}.btn-cancel:hover{filter:brightness(.95)}
  .btn-danger{background:#e74c3c;color:#fff}.btn-danger:hover{background:#c0392b}
  .btn-back{background:#fff;border:1px solid var(--borde);color:#70472e}.btn-back:hover{background:#f2e8db}
  .row{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:10px 0}
  .chip{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:700}
  .borrador{border-color:#999;color:#666}
  .enviada{border-color:var(--warn);color:#9a7d0a}
  .cerrada{border-color:var(--ok);color:var(--ok)}
  .cancelada{border-color:var(--bad);color:var(--bad)}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:10px;text-align:center}
  .table th{background:var(--cafe);color:#fff}
  .table tr:nth-child(even){background:#faf6ef}
  .form-inline{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
  .form-inline select,.form-inline input{border:1px solid var(--borde);border-radius:10px;padding:8px 10px}
  .line{height:1px;background:var(--borde);margin:12px 0}
  @media(max-width:820px){
    .table thead{display:none}
    .table tr{display:block;border:1px solid var(--borde);margin-bottom:10px;border-radius:10px;overflow:hidden}
    .table td{display:flex;justify-content:space-between;gap:12px;border:none;border-bottom:1px solid #eee}
    .table td:last-child{border-bottom:none}
    .table td::before{content:attr(data-label);font-weight:700;color:#7a6b5f}
  }
</style>

@if(session('success')) <div class="card" style="border-color:#c3e6cb;background:#d4edda;color:#155724">{{ session('success') }}</div>@endif
@if(session('error'))   <div class="card" style="border-color:#f5c6cb;background:#f8d7da;color:#721c24">{{ session('error') }}</div>@endif

<h1 class="page">Lista #{{ $lista->id }}</h1>
<div class="muted">
  Creador: {{ $lista->creador->name ?? '-' }} · Creada: {{ $lista->created_at->format('d/m/Y H:i') }}
</div>

<div class="row">
  <div>Estatus: <span class="chip {{ $lista->status }}">{{ ucfirst($lista->status) }}</span></div>
  <div>Comentario: <strong>{{ $lista->comentario ?? '—' }}</strong></div>
  <div>Total estimado: <strong>${{ number_format($lista->total_estimado,2) }}</strong></div>
</div>

<div class="row">
  <a class="btn btn-back" href="{{ route('listas.index') }}">← Volver</a>

  @if($lista->status==='borrador')
    <form method="POST" action="{{ route('listas.enviar',$lista->id) }}">@csrf
      <button class="btn btn-warn" type="submit">Enviar</button>
    </form>
    <form method="POST" action="{{ route('listas.cancelar',$lista->id) }}">@csrf
      <button class="btn btn-cancel" type="submit">Cancelar</button>
    </form>
    <form method="POST" action="{{ route('listas.destroy',$lista->id) }}"
          onsubmit="return confirm('¿Eliminar lista en borrador?')">
      @csrf @method('DELETE')
      <button class="btn btn-danger" type="submit">Eliminar</button>
    </form>
  @elseif($lista->status==='enviada')
    <form method="POST" action="{{ route('listas.cerrar',$lista->id) }}">@csrf
      <button class="btn btn-ok" type="submit">Cerrar</button>
    </form>
    <form method="POST" action="{{ route('listas.cancelar',$lista->id) }}">@csrf
      <button class="btn btn-cancel" type="submit">Cancelar</button>
    </form>
  @endif
</div>

<div class="line"></div>

<div class="card">
  <h3 style="margin-top:0">Materiales</h3>
  <table class="table">
    <thead>
      <tr>
        <th>#</th><th>Producto</th><th>Cantidad</th><th>Proveedor</th>
        <th>Precio estimado</th><th>Importe</th>@if($lista->status==='borrador')<th>Acciones</th>@endif
      </tr>
    </thead>
    <tbody>
      @forelse($lista->items as $it)
        <tr>
          <td data-label="#"> {{ $it->id }}</td>
          <td data-label="Producto">{{ $it->producto->nombre ?? '-' }}</td>
          <td data-label="Cantidad">{{ $it->cantidad }}</td>
          <td data-label="Proveedor">{{ $it->proveedor->nombre ?? '—' }}</td>
          <td data-label="Precio est.">{{ is_null($it->precio_estimado) ? '—' : '$'.number_format($it->precio_estimado,2) }}</td>
          <td data-label="Importe">
            @if(!is_null($it->precio_estimado))
              ${{ number_format($it->precio_estimado * $it->cantidad, 2) }}
            @else — @endif
          </td>

          @if($lista->status==='borrador')
            <td data-label="Acciones">
              {{-- Actualizar ítem --}}
              <form method="POST" action="{{ route('listas.items.update', [$lista->id, $it->id]) }}" class="form-inline" style="margin-bottom:6px">
                @csrf @method('PUT')
                <input type="number" name="cantidad" value="{{ $it->cantidad }}" min="1" style="width:100px">
                <input type="number" name="precio_estimado" value="{{ $it->precio_estimado }}" step="0.01" min="0" style="width:130px">
                <select name="proveedor_id" style="min-width:180px">
                  <option value="">—</option>
                  @foreach($proveedors as $p)
                    <option value="{{ $p->id }}" {{ $it->proveedor_id==$p->id?'selected':'' }}>{{ $p->nombre }}</option>
                  @endforeach
                </select>
                <button class="btn btn-primary" type="submit">Guardar</button>
              </form>

              {{-- Eliminar ítem --}}
              <form method="POST" action="{{ route('listas.items.destroy', [$lista->id, $it->id]) }}"
                    onsubmit="return confirm('¿Eliminar este ítem?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger" type="submit">Eliminar</button>
              </form>
            </td>
          @endif
        </tr>
      @empty
        <tr>
          <td colspan="{{ $lista->status==='borrador' ? 7 : 6 }}" style="text-align:center;color:#7a6b5f">
            No hay materiales en esta lista.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($lista->status==='borrador')
  <div class="line"></div>

  <div class="card">
    <h3 style="margin-top:0">Agregar material</h3>
    <form method="POST" action="{{ route('listas.items.store', $lista->id) }}" class="form-inline">
      @csrf
      <div>
        <label>Producto</label>
        <select name="producto_id" required style="min-width:260px">
          <option value="">— Selecciona —</option>
          @foreach($productos as $prod)
            <option value="{{ $prod->id }}">{{ $prod->nombre }} (Stock: {{ $prod->existencias }})</option>
          @endforeach
        </select>
      </div>

      <div>
        <label>Cantidad</label>
        <input type="number" name="cantidad" min="1" required style="width:120px">
      </div>

      <div>
        <label>Proveedor (opcional)</label>
        <select name="proveedor_id" style="min-width:200px">
          <option value="">—</option>
          @foreach($proveedors as $prov)
            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label>Precio estimado (opcional)</label>
        <input type="number" step="0.01" min="0" name="precio_estimado" style="width:160px">
      </div>

      <div style="align-self:flex-end">
        <button class="btn btn-primary" type="submit">Agregar</button>
      </div>
    </form>
  </div>
@endif
@endsection
