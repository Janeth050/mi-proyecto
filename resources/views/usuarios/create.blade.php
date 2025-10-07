@extends('layouts.app')
@section('content')
<h1 class="text-xl font-semibold mb-4">Nuevo usuario</h1>

<form method="POST" action="{{ route('usuarios.store') }}" class="grid md:grid-cols-2 gap-4 bg-white p-4 rounded">
    @csrf
    <div><label class="block text-sm font-medium mb-1">Nombre</label><input name="name" required class="border rounded p-2 w-full"></div>
    <div><label class="block text-sm font-medium mb-1">Email</label><input name="email" type="email" required class="border rounded p-2 w-full"></div>
    <div>
        <label class="block text-sm font-medium mb-1">Rol</label>
        <select name="role" class="border rounded p-2 w-full" required>
            <option value="empleado">Empleado</option>
            <option value="admin">Administrador</option>
        </select>
    </div>
    <div><label class="block text-sm font-medium mb-1">Contraseña</label><input name="password" type="password" required class="border rounded p-2 w-full"></div>
    <div><label class="block text-sm font-medium mb-1">Confirmar contraseña</label><input name="password_confirmation" type="password" required class="border rounded p-2 w-full"></div>

    <div class="md:col-span-2">
        <button class="px-4 py-2 rounded bg-[#6D4C41] text-white">Guardar</button>
        <a href="{{ route('usuarios.index') }}" class="ml-2 px-4 py-2 rounded border">Cancelar</a>
    </div>
</form>
@endsection
