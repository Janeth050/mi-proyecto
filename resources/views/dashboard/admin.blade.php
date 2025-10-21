@extends('layouts.app')

@section('title','Dashboard')

@section('content')
<style>
  :root{--cafe:#8b5e3c;--hover:#70472e;--beige:#f9f3e9;--texto:#5c3a21;--borde:#d9c9b3;--ok:#2ecc71;--bad:#e74c3c}
  .grid{display:grid;gap:14px}
  .cards{grid-template-columns:repeat(4,1fr)}
  .two{grid-template-columns:1fr 1fr}
  .card{background:#fff;border:1px solid var(--borde);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px}
  .kpi{font-size:12px;color:#7a6b5f;margin:0}
  .kpinum{font-size:28px;font-weight:800;color:var(--cafe);margin-top:4px}
  h1.page{color:var(--cafe);margin:0 0 14px;font-size:32px;font-weight:800}
  h3.cardtitle{margin:0 0 10px;color:#5c3a21}

  /* tablas */
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid var(--borde);padding:8px;text-align:center}
  .table th{background:var(--cafe);color:#fff}
  .table tr:nth-child(even){background:#faf6ef}
  .chip{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid var(--borde);font-weight:600}
  .chip.entrada{border-color:var(--ok);color:var(--ok)}
  .chip.salida{border-color:var(--bad);color:var(--bad)}
  .alerta{color:#fff;background:var(--bad);padding:4px 8px;border-radius:999px}

  /* contenedor de gráficas: evita que crezcan */
  .chart-card{display:block}
  .chart-box{position:relative;height:320px; /* ALTURA CONTROLADA */ }
  .chart-box canvas{width:100% !important;height:100% !important;display:block}

  /* responsive */
  @media (max-width: 1100px){ .cards{grid-template-columns:repeat(2,1fr)} }
  @media (max-width: 760px){
    .cards{grid-template-columns:1fr}
    .two{grid-template-columns:1fr}
    .table thead{display:none}
    .table tr{display:block;border:1px solid var(--borde);margin-bottom:8px;border-radius:10px;overflow:hidden}
    .table td{display:flex;justify-content:space-between;gap:10px;border:none;border-bottom:1px solid #eee}
    .table td:last-child{border-bottom:none}
    .table td::before{content:attr(data-label);font-weight:700;color:#7a6b5f}
  }
</style>

<h1 class="page">Dashboard</h1>

{{-- KPIs --}}
<div class="grid cards">
  <div class="card"><p class="kpi">Productos totales</p><div class="kpinum">{{ $resumen['productos_total'] }}</div></div>
  <div class="card"><p class="kpi">Bajo stock</p><div class="kpinum">{{ $resumen['bajo_stock'] }}</div></div>
  <div class="card"><p class="kpi">Entradas hoy</p><div class="kpinum">{{ $resumen['entradas_hoy'] }}</div></div>
  <div class="card"><p class="kpi">Salidas hoy</p><div class="kpinum">{{ $resumen['salidas_hoy'] }}</div></div>
</div>

{{-- Bajo stock + Últimos movimientos --}}
<div class="grid two" style="margin-top:14px">
  <div class="card">
    <h3 class="cardtitle">Alerta: Bajo stock</h3>
    <table class="table">
      <thead><tr><th>Producto</th><th>Unidad</th><th>Existencias</th><th>Mínimo</th></tr></thead>
      <tbody>
        @forelse($bajo as $p)
          <tr>
            <td data-label="Producto">{{ $p->nombre }}</td>
            <td data-label="Unidad">{{ $p->unidad->clave ?? '-' }}</td>
            <td data-label="Existencias"><span class="alerta">{{ $p->existencias }}</span></td>
            <td data-label="Mínimo">{{ $p->stock_minimo }}</td>
          </tr>
        @empty
          <tr><td colspan="4" style="text-align:center">No hay productos en alerta 🎉</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="card">
    <h3 class="cardtitle">Últimos movimientos</h3>
    <table class="table">
      <thead><tr><th>Fecha</th><th>Tipo</th><th>Producto</th><th>Cant.</th><th>Usuario</th></tr></thead>
      <tbody>
        @forelse($ultimos as $m)
          <tr>
            <td data-label="Fecha">{{ $m->created_at->format('d/m/Y H:i') }}</td>
            <td data-label="Tipo"><span class="chip {{ $m->tipo }}">{{ ucfirst($m->tipo) }}</span></td>
            <td data-label="Producto">{{ $m->producto->nombre ?? '-' }}</td>
            <td data-label="Cant.">{{ $m->cantidad }}</td>
            <td data-label="Usuario">{{ $m->usuario->name ?? '-' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" style="text-align:center">Sin movimientos recientes.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Gráficas (con contenedor de altura fija) --}}
<div class="grid two" style="margin-top:14px">
  <div class="card chart-card">
    <h3 class="cardtitle">Top usados (salidas) — 30 días</h3>
    <div class="chart-box"><canvas id="topUsados"></canvas></div>
  </div>
  <div class="card chart-card">
    <h3 class="cardtitle">Gasto mensual (entradas) — 6 meses</h3>
    <div class="chart-box"><canvas id="gastoMensual"></canvas></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const topLabels   = @json($topLabels);
  const topData     = @json($topData);
  const gastoLabels = @json($gastoLabels);
  const gastoData   = @json($gastoData);

  new Chart(document.getElementById('topUsados'), {
    type: 'bar',
    data: { labels: topLabels, datasets: [{ label: 'Unidades salidas', data: topData }] },
    options: {
      responsive: true,
      maintainAspectRatio: false, // <— clave
      scales: { y: { beginAtZero: true } },
      plugins: { legend: { display: true } }
    }
  });

  new Chart(document.getElementById('gastoMensual'), {
    type: 'line',
    data: { labels: gastoLabels, datasets: [{ label: 'Gasto ($)', data: gastoData, tension: .2 }] },
    options: {
      responsive: true,
      maintainAspectRatio: false, // <— clave
      scales: { y: { beginAtZero: true } },
      plugins: { legend: { display: true } }
    }
  });
</script>
@endsection
