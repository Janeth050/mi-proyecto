@php $esAdmin = auth()->check() && auth()->user()->role === 'admin'; @endphp

<nav class="flex gap-4 items-center">
  <a href="{{ route('dashboard') }}">Dashboard</a>
  <a href="{{ route('productos.index') }}">Productos</a>
  <a href="{{ route('movimientos.index') }}">Movimientos</a>

  @can('admin')
    <a href="{{ route('proveedores.index') }}">Proveedores</a>
    <a href="{{ route('usuarios.index') }}">Usuarios</a>
    <a href="{{ route('listas.index') }}">Listas</a>
  @endcan
</nav>
