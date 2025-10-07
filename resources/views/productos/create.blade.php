<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Producto</title>
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
      max-width: 600px;
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

    input, select {
      width: 100%;
      padding: 10px;
      margin-top: 6px;
      border: 1px solid var(--borde);
      border-radius: 6px;
      font-size: 15px;
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

  <h1>Agregar Nuevo Producto</h1>

  <form action="{{ route('productos.store') }}" method="POST">
    @csrf

    <label for="codigo">Código:</label>
    <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" required>
    @error('codigo') <div class="error">{{ $message }}</div> @enderror

    <label for="nombre">Nombre del producto:</label>
    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required>
    @error('nombre') <div class="error">{{ $message }}</div> @enderror

    <label for="unidad_id">Unidad de medida:</label>
    <select name="unidad_id" id="unidad_id" required>
      <option value="">-- Selecciona unidad --</option>
      @foreach ($unidades as $unidad)
        <option value="{{ $unidad->id }}" {{ old('unidad_id') == $unidad->id ? 'selected' : '' }}>
          {{ $unidad->descripcion }}
        </option>
      @endforeach
    </select>
    @error('unidad_id') <div class="error">{{ $message }}</div> @enderror

    <label for="categoria_id">Categoría:</label>
    <select name="categoria_id" id="categoria_id">
      <option value="">-- Opcional --</option>
      @foreach ($categorias as $categoria)
        <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
          {{ $categoria->nombre }}
        </option>
      @endforeach
    </select>

    <label for="existencias">Existencias:</label>
    <input type="number" name="existencias" id="existencias" value="{{ old('existencias', 0) }}" min="0" required>

    <label for="stock_minimo">Stock mínimo:</label>
    <input type="number" name="stock_minimo" id="stock_minimo" value="{{ old('stock_minimo', 0) }}" min="0" required>

    <label for="costo_promedio">Costo promedio:</label>
    <input type="number" name="costo_promedio" id="costo_promedio" step="0.01" value="{{ old('costo_promedio', 0) }}" min="0">

    <button type="submit">Guardar producto</button>
    <br>
    <a href="{{ route('productos.index') }}">← Volver al listado</a>
  </form>

</body>
</html>
