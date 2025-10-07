<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Lista #{{ $lista->id }}</title>
  <style>
    :root{--cafe:#8b5e3c;--beige:#f9f3e9;--texto:#5c3a21;--borde:#d9c9b3;--hover:#70472e;--warn:#f1c40f;--ok:#2ecc71;--bad:#e74c3c}
    body{font-family:'Segoe UI',sans-serif;background:var(--beige);color:var(--texto);margin:0}
    .wrap{width:95%;max-width:1100px;margin:24px auto;background:#fff;border:1px solid var(--borde);border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);padding:16px}
    h1{color:var(--cafe);margin:0 0 8px}
    .muted{color:#7a6b5f}
    .row{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin:10px 0}
    .chip{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:600}
    .borrador{border-color:#999;color:#666}
    .enviada{border-color:var(--warn);color:#9a7d0a}
    .cerrada{border-color:var(--ok);color:var(--ok)}
    .cancelada{border-color:var(--bad);color:var(--bad)}
    .btn{background:var(--cafe);color:#fff;border:none;border-radius:8px;padding:8px 12px;text-decoration:none}
    .btn:hover{background:var(--hover)}
    .line{height:1px;background:var(--borde);margin:12px 0}
    table{width:100%;border-collapse:collapse;margin-top:8px}
    th,td{border:1px solid var(--borde);padding:8px;text-align:center}
    th{background:var(--cafe);color:#fff}
    tr:nth-child(even){background:#f7efe2}
    form{display:inline}
    select,input{border:1px solid var(--borde);border-radius:8px;padding:8px 10px}
  </style>
</head>
<body>
  <div class="wrap">
    @if(session('success')) <div style="background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:8px;padding:10px;margin-bottom:12px;text-align:center">{{ session('success') }}</div> @endif
    @if(session('error'))   <div style="background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;border-radius:8px;padding:10px;margin-bottom:12px;text-align:center">{{ session('error') }}</div> @endif

    <h1>Lista #{{ $lista->id }}</h1>
    <div class="muted">Creador: {{ $lista->creador->name ?? '-' }} · Creada: {{ $lista->created_at->format('d/m/Y H:i') }}</div>
    <div class="row">
      <div>Estatus: <span class="chip {{ $lista->status }}">{{ ucfirst($lista->status) }}</span></div>
      <div>Comentario: <strong>{{ $lista->comentario ?? '—' }}</strong></div>
      <div>Total estimado: <strong>${{ number_format($lista->total_estimado,2) }}</strong></div>
    </div>

    {{-- Acciones de estado --}}
    <div class="row">
      <a class="btn" href="{{ route('listas.index') }}">← Volver</a>

      @if($lista->status==='borrador')
        <form method="POST" action="{{ route('listas.enviar',$lista->id) }}">@csrf
          <button class="btn" type="submit" style="background:#f1c40f;color:#333">Enviar</button>
        </form>
        <form method="POST" action="{{ route('listas.cancelar',$lista->id) }}">@csrf
          <button class="btn" type="submit" style="background:#e67e22">Cancelar</button>
        </form>
        <form method="POST" action="{{ route('listas.destroy',$lista->id) }}" onsubmit="return confirm('¿Eliminar lista en borrador?')">
          @csrf @method('DELETE')
          <button class="btn" type="submit" style="background:#e74c3c">Eliminar</button>
        </form>
      @elseif($lista->status==='enviada')
        <form method="POST" action="{{ route('listas.cerrar',$lista->id) }}">@csrf
          <button class="btn" type="submit" style="background:#2ecc71">Cerrar</button>
        </form>
        <form method="POST" action="{{ route('listas.cancelar',$lista->id) }}">@csrf
          <button class="btn" type="submit" style="background:#e67e22">Cancelar</button>
        </form>
      @endif
    </div>

    <div class="line"></div>

    {{-- Tabla de ítems --}}
    <h3>Materiales</h3>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Producto</th>
          <th>Cantidad</th>
          <th>Proveedor</th>
          <th>Precio estimado</th>
          <th>Importe</th>
          @if($lista->status==='borrador') <th>Acciones</th> @endif
        </tr>
      </thead>
      <tbody>
        @forelse($lista->items as $it)
          <tr>
            <td>{{ $it->id }}</td>
            <td>{{ $it->producto->nombre ?? '-' }}</td>
            <td>{{ $it->cantidad }}</td>
            <td>{{ $it->proveedor->nombre ?? '—' }}</td>
            <td>{{ is_null($it->precio_estimado) ? '—' : '$'.number_format($it->precio_estimado,2) }}</td>
            <td>
              @if(!is_null($it->precio_estimado))
                ${{ number_format($it->precio_estimado * $it->cantidad, 2) }}
              @else —
              @endif
            </td>
            @if($lista->status==='borrador')
              <td>
                {{-- Actualizar ítem --}}
                <form method="POST" action="{{ route('listas.items.update', [$lista->id, $it->id]) }}">
                  @csrf @method('PUT')
                  <input type="number" name="cantidad" value="{{ $it->cantidad }}" min="1" style="width:90px">
                  <input type="number" name="precio_estimado" value="{{ $it->precio_estimado }}" step="0.01" min="0" style="width:120px">
                  <select name="proveedor_id">
                    <option value="">—</option>
                    @foreach($proveedors as $p)
                      <option value="{{ $p->id }}" {{ $it->proveedor_id==$p->id?'selected':'' }}>{{ $p->nombre }}</option>
                    @endforeach
                  </select>
                  <button class="btn" type="submit">Guardar</button>
                </form>
                {{-- Eliminar ítem --}}
                <form method="POST" action="{{ route('listas.items.destroy', [$lista->id, $it->id]) }}" onsubmit="return confirm('¿Eliminar este ítem?')">
                  @csrf @method('DELETE')
                  <button class="btn" type="submit" style="background:#e74c3c">Eliminar</button>
                </form>
              </td>
            @endif
          </tr>
        @empty
          <tr><td colspan="{{ $lista->status==='borrador' ? 7 : 6 }}">No hay materiales en esta lista.</td></tr>
        @endforelse
      </tbody>
    </table>

    {{-- Formulario para agregar ítems (solo en borrador) --}}
    @if($lista->status==='borrador')
      <div class="line"></div>
      <h3>Agregar material</h3>
      <form method="POST" action="{{ route('listas.items.store', $lista->id) }}" class="row">
        @csrf
        <div>
          <label>Producto:</label>
          <select name="producto_id" required>
            <option value="">— Selecciona —</option>
            @foreach($productos as $prod)
              <option value="{{ $prod->id }}">{{ $prod->nombre }} (Stock: {{ $prod->existencias }})</option>
            @endforeach
          </select>
        </div>
        <div>
          <label>Cantidad:</label>
          <input type="number" name="cantidad" min="1" required>
        </div>
        <div>
          <label>Proveedor (opcional):</label>
          <select name="proveedor_id">
            <option value="">—</option>
            @foreach($proveedors as $prov)
              <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label>Precio estimado (opcional):</label>
          <input type="number" step="0.01" min="0" name="precio_estimado">
        </div>
        <div style="align-self:flex-end">
          <button class="btn" type="submit">Agregar</button>
        </div>
      </form>
    @endif

  </div>
</body>
</html>
