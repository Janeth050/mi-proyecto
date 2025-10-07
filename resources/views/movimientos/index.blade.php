<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Movimientos de Inventario</title>
  <style>
    :root {
      --cafe:#8b5e3c; --beige:#f9f3e9; --texto:#5c3a21; --borde:#d9c9b3; --hover:#70472e;
      --ok:#2ecc71; --warn:#f1c40f; --bad:#e74c3c; --chip:#ffffff;
    }
    *{box-sizing:border-box}
    body{font-family:'Segoe UI',sans-serif;background:var(--beige);color:var(--texto);margin:0}
    h1{text-align:center;color:var(--cafe);margin:24px 0}
    .wrap{width:95%;max-width:1200px;margin:0 auto 28px;background:#fff;border:1px solid var(--borde);
          border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);padding:16px}
    .topbar{display:flex;gap:12px;justify-content:space-between;align-items:center;flex-wrap:wrap}
    .acciones a, .acciones button, .btn{background:var(--cafe);color:#fff;border:none;border-radius:8px;
          padding:8px 12px;text-decoration:none;cursor:pointer}
    .btn:hover,.acciones button:hover,.acciones a:hover{background:var(--hover)}
    .mensaje{background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:8px;padding:10px;margin-bottom:12px;text-align:center}
    .filtros{display:flex;gap:8px;flex-wrap:wrap}
    .filtros input, .filtros select{border:1px solid var(--borde);border-radius:8px;padding:8px 10px}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{border:1px solid var(--borde);padding:10px;text-align:center}
    th{background:var(--cafe);color:#fff;position:sticky;top:0}
    tr:nth-child(even){background:#f7efe2}
    .chip{display:inline-block;padding:4px 10px;border-radius:999px;background:var(--chip);border:1px solid var(--borde);font-weight:600}
    .entrada{border-color:var(--ok);color:var(--ok)}
    .salida{border-color:var(--bad);color:var(--bad)}
    .pendiente{border-color:var(--warn);color:#9a7d0a}
    .confirmado{border-color:var(--ok);color:var(--ok)}
    .cancelado{border-color:var(--bad);color:var(--bad)}
    .acciones{display:flex;gap:8px;justify-content:center;align-items:center}
    form{display:inline}
  </style>
</head>
<body>

  <h1>Movimientos de Inventario</h1>

  <div class="wrap">
    {{-- Mensaje de éxito --}}
    @if(session('success'))
      <div class="mensaje">{{ session('success') }}</div>
    @endif

    <div class="topbar">
      <div class="filtros">
        {{-- Filtros simples (opcionales, no rompen nada si no los usas aún) --}}
        <form action="{{ route('movimientos.index') }}" method="GET" style="display:flex;gap:8px;flex-wrap:wrap">
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

          <input type="text" name="q" placeholder="Buscar por producto/usuario"
                 value="{{ request('q') }}" />

          <button class="btn" type="submit">Filtrar</button>
          <a class="btn" href="{{ route('movimientos.index') }}" style="background:#6c757d">Limpiar</a>
        </form>
      </div>

      <a class="btn" href="{{ route('movimientos.create') }}">+ Registrar movimiento</a>
    </div>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Producto</th>
          <th>Tipo</th>
          <th>Cantidad</th>
          <th>Existencia después</th>
          <th>Usuario</th>
          <th>Proveedor</th>
          <th>Costo total</th>
          <th>Estatus</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($movimientos as $mov)
          <tr>
            <td>{{ $mov->id }}</td>
            <td>{{ $mov->producto->nombre ?? '—' }}</td>
            <td>
              <span class="chip {{ $mov->tipo }}">
                {{ ucfirst($mov->tipo) }}
              </span>
            </td>
            <td>{{ $mov->cantidad }}</td>
            <td>{{ $mov->existencias_despues }}</td>
            <td>{{ $mov->usuario->name ?? '—' }}</td>
            <td>{{ $mov->proveedor->nombre ?? '—' }}</td>
            <td>
              @if(!is_null($mov->costo_total))
                ${{ number_format($mov->costo_total, 2) }}
              @else
                —
              @endif
            </td>
            <td>
              <span class="chip {{ $mov->status }}">
                {{ ucfirst($mov->status) }}
              </span>
            </td>
            <td>{{ $mov->created_at->format('d/m/Y H:i') }}</td>
            <td class="acciones">
              {{-- Ver (opcional si usas movimientos.show) --}}
              @if(Route::has('movimientos.show'))
                <a class="btn" href="{{ route('movimientos.show', $mov->id) }}">Ver</a>
              @endif
              {{-- Aquí podrías agregar acciones de cancelar/confirmar si las implementas luego --}}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="11">No hay movimientos registrados.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</body>
</html>
