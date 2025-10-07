<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Empleado</title>
<style>
  :root{--cafe:#8b5e3c;--beige:#f9f3e9;--texto:#5c3a21;--borde:#d9c9b3;--hover:#70472e;--ok:#2ecc71;--bad:#e74c3c}
  body{font-family:'Segoe UI',sans-serif;background:var(--beige);color:var(--texto);margin:0}
  h1{color:var(--cafe);margin:20px;text-align:center}
  .wrap{width:95%;max-width:1000px;margin:0 auto 28px;background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 8px 20px rgba(0,0,0,.06);padding:16px}
  table{width:100%;border-collapse:collapse}
  th,td{border:1px solid var(--borde);padding:8px;text-align:center}
  th{background:var(--cafe);color:#fff}
  tr:nth-child(even){background:#f7efe2}
  .chip{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:600}
  .entrada{border-color:var(--ok);color:var(--ok)}
  .salida{border-color:var(--bad);color:var(--bad)}
</style>
</head>
<body>
  <h1>Panel de Empleado</h1>
  <div class="wrap">
    <h3 style="margin-top:0;">Mis últimos movimientos</h3>
    <table>
      <thead>
        <tr><th>Fecha</th><th>Tipo</th><th>Producto</th><th>Cantidad</th></tr>
      </thead>
      <tbody>
        @forelse($ultimos as $m)
          <tr>
            <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
            <td><span class="chip {{ $m->tipo }}">{{ ucfirst($m->tipo) }}</span></td>
            <td>{{ $m->producto->nombre ?? '-' }}</td>
            <td>{{ $m->cantidad }}</td>
          </tr>
        @empty
          <tr><td colspan="4">Aún no registras movimientos.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</body>
</html>
