<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Detalles del Producto</title>
  <style>
    :root {
      --cafe: #8b5e3c;
      --beige: #f9f3e9;
      --texto: #5c3a21;
      --borde: #d9c9b3;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--beige);
      color: var(--texto);
      margin: 0;
      padding: 0;
    }

    .card {
      width: 500px;
      background: white;
      margin: 40px auto;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border: 1px solid var(--borde);
    }

    h1 {
      color: var(--cafe);
      text-align: center;
    }

    p {
      font-size: 16px;
      margin: 8px 0;
    }

    strong {
      color: var(--cafe);
    }

    a {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      color: var(--cafe);
      font-weight: 600;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="card">
    <h1>Detalles del Producto</h1>

    <p><strong>Código:</strong> {{ $producto->codigo }}</p>
    <p><strong>Nombre:</strong> {{ $producto->nombre }}</p>
    <p><strong>Unidad:</strong> {{ $producto->unidad->descripcion ?? '-' }}</p>
    <p><strong>Categoría:</strong> {{ $producto->categoria->nombre ?? '-' }}</p>
    <p><strong>Existencias:</strong> {{ $producto->existencias }}</p>
    <p><strong>Stock mínimo:</strong> {{ $producto->stock_minimo }}</p>
    <p><strong>Costo promedio:</strong> ${{ number_format($producto->costo_promedio, 2) }}</p>
    <p><strong>Última actualización:</strong> {{ $producto->updated_at->format('d/m/Y H:i') }}</p>

    <a href="{{ route('productos.index') }}">← Volver al listado</a>
  </div>

</body>
</html>
