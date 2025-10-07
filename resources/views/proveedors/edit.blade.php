<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Proveedor</title>
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

    input, textarea {
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

  <h1>Editar Proveedor</h1>

  <form action="{{ route('proveedors.update', $proveedor->id) }}" method="POST">
    @csrf
    @method('PUT')

    <label for="nombre">Nombre del proveedor:</label>
    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $proveedor->nombre) }}" required>
    @error('nombre') <div class="error">{{ $message }}</div> @enderror

    <label for="telefono">Teléfono:</label>
    <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $proveedor->telefono) }}">

    <label for="correo">Correo electrónico:</label>
    <input type="email" name="correo" id="correo" value="{{ old('correo', $proveedor->correo) }}">

    <label for="direccion">Dirección:</label>
    <input type="text" name="direccion" id="direccion" value="{{ old('direccion', $proveedor->direccion) }}">

    <label for="notas">Notas adicionales:</label>
    <textarea name="notas" id="notas" rows="3">{{ old('notas', $proveedor->notas) }}</textarea>

    <button type="submit">Actualizar proveedor</button>
    <br>
    <a href="{{ route('proveedors.index') }}">← Volver al listado</a>
  </form>

</body>
</html>
