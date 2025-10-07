<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Listas de Pedido</title>
  <style>
    :root{--cafe:#8b5e3c;--beige:#f9f3e9;--texto:#5c3a21;--borde:#d9c9b3;--hover:#70472e;--ok:#2ecc71;--warn:#f1c40f;--bad:#e74c3c}
    body{font-family:'Segoe UI',sans-serif;background:var(--beige);color:var(--texto);margin:0}
    h1{text-align:center;color:var(--cafe);margin:24px 0}
    .wrap{width:95%;max-width:1100px;margin:0 auto 28px;background:#fff;border:1px solid var(--borde);border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);padding:16px}
    .top{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
    .btn{background:var(--cafe);color:#fff;border:none;border-radius:8px;padding:8px 12px;text-decoration:none}
    .btn:hover{background:var(--hover)}
    .mensaje{background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:8px;padding:10px;margin-bottom:12px;text-align:center}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{border:1px solid var(--borde);padding:8px;text-align:center}
    th{background:var(--cafe);color:#fff}
    tr:nth-child(even){background:#f7efe2}
    .chip{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:600}
    .borrador{border-color:#999;color:#666}
    .enviada{border-color:var(--warn);color:#9a7d0a}
    .cerrada{border-color:var(--ok);color:var(--ok)}
    .cancelada{border-color:var(--bad);color:var(--bad)}
    form{display:inline}
  </style>
</head>
<body>
  <h1>Listas de Pedido</h1>
  <div class="wrap">
    @if(session('success')) <div class="mensaje">{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="mensaje" style="background:#f8d7da;color:#721c24;border-color:#f5c6cb">{{ session('error') }}</div> @endif

    <div class="top">
      <a class="btn" href="{{ route('listas.create') }}">+ Nueva lista</a>
    </div>

    <table>
      <thead>
      <tr>
        <th>ID</th>
        <th>Creador</th>
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
          <td>{{ $l->id }}</td>
          <td>{{ $l->creador->name ?? '-' }}</td>
          <td>{{ $l->comentario ?? '-' }}</td>
          <td><span class="chip {{ $l->status }}">{{ ucfirst($l->status) }}</span></td>
          <td>{{ $l->items()->count() }}</td>
          <td>${{ number_format($l->total_estimado,2) }}</td>
          <td>{{ $l->created_at->format('d/m/Y H:i') }}</td>
          <td>
            <a class="btn" href="{{ route('listas.show', $l->id) }}">Ver</a>
            @if($l->status==='borrador')
              <form action="{{ route('listas.destroy',$l->id) }}" method="POST" onsubmit="return confirm('¿Eliminar lista en borrador?')">
                @csrf @method('DELETE')
                <button class="btn" style="background:#e74c3c">Eliminar</button>
              </form>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="8">No hay listas aún.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</body>
</html>
