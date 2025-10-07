<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Kardex de {{ $producto->nombre }}</title>
  <style>
    :root{--cafe:#8b5e3c;--beige:#f9f3e9;--texto:#5c3a21;--borde:#d9c9b3;--hover:#70472e}
    body{font-family:'Segoe UI',sans-serif;background:var(--beige);color:var(--texto);margin:0}
    .wrap{width:95%;max-width:1100px;margin:24px auto;background:#fff;border:1px solid var(--borde);
          border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);padding:16px}
    h1{color:var(--cafe);margin:0 0 12px}
    .muted{color:#7a6b5f}
    .filtros{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
    input,button,a{border:1px solid var(--borde);border-radius:8px;padding:8px 10px}
    button,.btn{background:var(--cafe);color:#fff;border:none;cursor:pointer}
    button:hover,.btn:hover{background:var(--hover)}
    table{width:100%;border-collapse:collapse}
    th,td{border:1px solid var(--borde);padding:8px;text-align:center}
    th{background:var(--cafe);color:#fff}
    tr:nth-child(even){background:#f7efe2}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Kardex de: {{ $producto->nombre }}</h1>
    <div class="muted">Código: {{ $producto->codigo }} · Unidad: {{ $producto->unidad->descripcion ?? '-' }} · Existencias actuales: <strong>{{ $producto->existencias }}</strong></div>

    <form class="filtros" action="{{ route('kardex.producto', $producto->id) }}" method="GET">
      <label>Desde: <input type="date" name="desde" value="{{ request('desde') }}"></label>
      <label>Hasta: <input type="date" name="hasta" value="{{ request('hasta') }}"></label>
      <button type="submit">Filtrar</button>
      <a class="btn" href="{{ route('kardex.producto', $producto->id) }}" style="background:#6c757d">Limpiar</a>
      <a class="btn" href="{{ route('productos.index') }}">← Volver a productos</a>
    </form>

    <table>
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
            <td>{{ $k->fecha?->format('d/m/Y H:i') }}</td>
            <td class="muted">
              @if($k->movimiento)
                #{{ $k->movimiento->id }} ({{ ucfirst($k->movimiento->tipo) }})
              @else — @endif
            </td>
            <td>{{ ucfirst($k->tipo) }}</td>
            <td>{{ $k->entrada }}</td>
            <td>{{ $k->salida }}</td>
            <td><strong>{{ $k->saldo }}</strong></td>
            <td>@if(!is_null($k->costo_unitario)) ${{ number_format($k->costo_unitario, 4) }} @else — @endif</td>
            <td>@if(!is_null($k->costo_total)) ${{ number_format($k->costo_total, 4) }} @else — @endif</td>
          </tr>
        @empty
          <tr><td colspan="8">No hay registros del período seleccionado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</body>
</html>
