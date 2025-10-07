@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Admin - Inventario</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  :root{--cafe:#8b5e3c;--beige:#f9f3e9;--texto:#5c3a21;--borde:#d9c9b3;--hover:#70472e;--ok:#2ecc71;--warn:#f1c40f;--bad:#e74c3c}
  *{box-sizing:border-box}
  body{font-family:'Segoe UI',sans-serif;background:var(--beige);color:var(--texto);margin:0}
  h1{color:var(--cafe);margin:20px;text-align:center}
  .wrap{width:95%;max-width:1200px;margin:0 auto 28px}
  .grid{display:grid;gap:14px}
  .cards{grid-template-columns:repeat(4,1fr)}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 8px 20px rgba(0,0,0,.06);padding:16px}
  .kpi{font-size:12px;color:#7a6b5f}
  .kpinum{font-size:28px;font-weight:700;color:var(--cafe)}
  .two{grid-template-columns:1fr 1fr}
  table{width:100%;border-collapse:collapse}
  th,td{border:1px solid var(--borde);padding:8px;text-align:center}
  th{background:var(--cafe);color:#fff}
  tr:nth-child(even){background:#f7efe2}
  .chip{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:600}
  .entrada{border-color:var(--ok);color:var(--ok)}
  .salida{border-color:var(--bad);color:var(--bad)}
  .alerta{color:#fff;background:var(--bad);padding:4px 8px;border-radius:999px}
</style>
</head>
<body>

<h1>Panel de Administración</h1>
<div class="wrap grid">
  <!-- KPIs -->
  <div class="grid cards">
    <div class="card"><div class="kpi">Productos totales</div><div class="kpinum">{{ $resumen['productos_total'] }}</div></div>
    <div class="card"><div class="kpi">Bajo stock</div><div class="kpinum">{{ $resumen['bajo_stock'] }}</div></div>
    <div class="card"><div class="kpi">Entradas hoy</div><div class="kpinum">{{ $resumen['entradas_hoy'] }}</div></div>
    <div class="card"><div class="kpi">Salidas hoy</div><div class="kpinum">{{ $resumen['salidas_hoy'] }}</div></div>
  </div>

  <!-- Bajo stock + Últimos movimientos -->
  <div class="grid two">
    <div class="card">
      <h3 style="margin-top:0;">Alerta: Bajo stock</h3>
      <table>
        <thead>
          <tr><th>Producto</th><th>Unidad</th><th>Existencias</th><th>Mínimo</th></tr>
        </thead>
        <tbody>
        @forelse($bajo as $p)
          <tr>
            <td>{{ $p->nombre }}</td>
            <td>{{ $p->unidad->clave ?? '-' }}</td>
            <td><span class="alerta">{{ $p->existencias }}</span></td>
            <td>{{ $p->stock_minimo }}</td>
          </tr>
        @empty
          <tr><td colspan="4">No hay productos en alerta 🎉</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    <div class="card">
      <h3 style="margin-top:0;">Últimos movimientos</h3>
      <table>
        <thead>
          <tr><th>Fecha</th><th>Tipo</th><th>Producto</th><th>Cant.</th><th>Usuario</th></tr>
        </thead>
        <tbody>
        @forelse($ultimos as $m)
          <tr>
            <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
            <td><span class="chip {{ $m->tipo }}">{{ ucfirst($m->tipo) }}</span></td>
            <td>{{ $m->producto->nombre ?? '-' }}</td>
            <td>{{ $m->cantidad }}</td>
            <td>{{ $m->usuario->name ?? '-' }}</td>
          </tr>
        @empty
          <tr><td colspan="5">Sin movimientos recientes.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Gráficas -->
  <div class="grid two">
    <div class="card">
      <h3 style="margin-top:0;">Top usados (salidas) — 30 días</h3>
      <canvas id="topUsados"></canvas>
    </div>
    <div class="card">
      <h3 style="margin-top:0;">Gasto mensual (entradas) — 6 meses</h3>
      <canvas id="gastoMensual"></canvas>
    </div>
  </div>
</div>

<script>
  // Datos desde el controlador
  const topLabels = @json($topLabels);
  const topData   = @json($topData);
  const gastoLabels = @json($gastoLabels);
  const gastoData   = @json($gastoData);

  // Gráfica Top usados (barra)
  new Chart(document.getElementById('topUsados'), {
    type: 'bar',
    data: {
      labels: topLabels,
      datasets: [{
        label: 'Unidades salidas',
        data: topData
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: true } },
      scales: { y: { beginAtZero: true } }
    }
  });

  // Gráfica Gasto mensual (línea)
  new Chart(document.getElementById('gastoMensual'), {
    type: 'line',
    data: {
      labels: gastoLabels,
      datasets: [{
        label: 'Gasto ($)',
        data: gastoData,
        tension: 0.2,
        fill: false
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: true } },
      scales: { y: { beginAtZero: true } }
    }
  });
</script>
</body>
</html>
@endsection
