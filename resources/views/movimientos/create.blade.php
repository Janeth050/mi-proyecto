<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Movimiento</title>
  <style>
    :root {
      --cafe: #8b5e3c;
      --beige: #f9f3e9;
      --texto: #5c3a21;
      --borde: #d9c9b3;
      --hover: #70472e;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--beige);
      color: var(--texto);
      margin: 0;
      padding: 0;
    }

    h1 {
      text-align: center;
      color: var(--cafe);
      margin-top: 30px;
    }

    form {
      max-width: 650px;
      background: white;
      margin: 30px auto;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border: 1px solid var(--borde);
    }

    label {
      display: block;
      margin-top: 12px;
      font-weight: 600;
    }

    select, input, textarea {
      width: 100%;
      padding: 10px;
      margin-top: 6px;
      border: 1px solid var(--borde);
      border-radius: 6px;
      font-size: 15px;
    }

    textarea {
      resize: none;
    }

    button {
      background: var(--cafe);
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 6px;
      margin-top: 20px;
      cursor: pointer;
      font-size: 16px;
    }

    button:hover {
      background: var(--hover);
    }

    a {
      display: inline-block;
      margin-top: 15px;
      color: var(--cafe);
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    .error {
      color: red;
      font-size: 14px;
    }
  </style>
</head>
<body>

  <h1>Registrar Movimiento de Inventario</h1>

  <form action="{{ route('movimientos.store') }}" method="POST">
    @csrf

    <!-- Producto -->
    <label for="producto_id">Producto:</label>
    <select name="producto_id" id="producto_id" required>
      <option value="">-- Selecciona producto --</option>
      @foreach ($productos as $producto)
        <option value="{{ $producto->id }}" {{ old('producto_id') == $producto->id ? 'selected' : '' }}>
          {{ $producto->nombre }} (Stock: {{ $producto->existencias }})
        </option>
      @endforeach
    </select>
    @error('producto_id') <div class="error">{{ $message }}</div> @enderror

    <!-- Tipo de movimiento -->
    <label for="tipo">Tipo de movimiento:</label>
    <select name="tipo" id="tipo" required>
      <option value="">-- Selecciona tipo --</option>
      <option value="entrada" {{ old('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
      <option value="salida" {{ old('tipo') == 'salida' ? 'selected' : '' }}>Salida</option>
    </select>
    @error('tipo') <div class="error">{{ $message }}</div> @enderror

    <!-- Cantidad -->
    <label for="cantidad">Cantidad:</label>
    <input type="number" name="cantidad" id="cantidad" value="{{ old('cantidad') }}" min="1" required>
    @error('cantidad') <div class="error">{{ $message }}</div> @enderror

    <!-- Proveedor (solo entradas) -->
    <label for="proveedor_id">Proveedor (solo si es entrada):</label>
    <select name="proveedor_id" id="proveedor_id">
      <option value="">-- Ninguno --</option>
      @foreach ($proveedors as $proveedor)
        <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
          {{ $proveedor->nombre }}
        </option>
      @endforeach
    </select>

    <!-- Costo unitario (opcional para entradas) -->
    <label for="costo_unitario">Costo unitario:</label>
    <input type="number" name="costo_unitario" id="costo_unitario" step="0.01" value="{{ old('costo_unitario') }}" min="0">

    <!-- Descripción -->
    <label for="descripcion">Descripción / motivo:</label>
    <textarea name="descripcion" id="descripcion" rows="3">{{ old('descripcion') }}</textarea>

    <button type="submit">Guardar movimiento</button>
    <br>
    <a href="{{ route('movimientos.index') }}">← Volver al listado</a>
  </form>

</body>
</html>
