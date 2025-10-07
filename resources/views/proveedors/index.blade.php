<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Lista de Proveedores</title>
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
      margin: 25px 0;
    }

    .contenedor {
      width: 90%;
      margin: 0 auto;
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border: 1px solid var(--borde);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th, td {
      border: 1px solid var(--borde);
      padding: 10px;
      text-align: center;
      font-size: 15px;
    }

    th {
      background-color: var(--cafe);
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f7efe2;
    }

    a.boton {
      background: var(--cafe);
      color: white;
      padding: 6px 10px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 14px;
    }

    a.boton:hover {
      background: var(--hover);
    }

    .acciones {
      display: flex;
      justify-content: center;
      gap: 10px;
    }

    form {
      display: inline;
    }

    button {
      background: #c0392b;
      color: white;
      border: none;
      border-radius: 6px;
      padding: 6px 10px;
      cursor: pointer;
      font-size: 14px;
    }

    button:hover {
      background: #a93226;
    }

    .nuevo {
      display: inline-block;
      margin-bottom: 15px;
      background: var(--cafe);
      color: white;
      padding: 8px 12px;
      border-radius: 6px;
      text-decoration: none;
    }

    .nuevo:hover {
      background: var(--hover);
    }

    .mensaje {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 15px;
      text-align: center;
    }
  </style>
</head>
<body>

  <h1>Lista de Proveedores</h1>

  <div class="contenedor">
    {{-- Mensaje de éxito --}}
    @if (session('success'))
      <div class="mensaje">
        {{ session('success') }}
      </div>
    @endif

    {{-- Botón para agregar nuevo proveedor --}}
    <a href="{{ route('proveedors.create') }}" class="nuevo">+ Nuevo proveedor</a>

    {{-- Tabla de proveedores --}}
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Teléfono</th>
          <th>Correo</th>
          <th>Dirección</th>
          <th>Notas</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($proveedors as $proveedor)
          <tr>
            <td>{{ $proveedor->id }}</td>
            <td>{{ $proveedor->nombre }}</td>
            <td>{{ $proveedor->telefono ?? '-' }}</td>
            <td>{{ $proveedor->correo ?? '-' }}</td>
            <td>{{ $proveedor->direccion ?? '-' }}</td>
            <td>{{ $proveedor->notas ?? '-' }}</td>
            <td class="acciones">
              <a href="{{ route('proveedors.edit', $proveedor->id) }}" class="boton">Editar</a>

              <form action="{{ route('proveedors.destroy', $proveedor->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este proveedor?');">
                @csrf
                @method('DELETE')
                <button type="submit">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7">No hay proveedores registrados aún.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

</body>
</html>
