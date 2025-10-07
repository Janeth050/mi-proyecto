@extends('layouts.app')
@section('content')
<h1 class="text-xl font-semibold mb-4">Usuarios</h1>

<div class="mb-3">
    <a href="{{ route('usuarios.create') }}" class="px-4 py-2 rounded bg-[#6D4C41] text-white">Nuevo usuario</a>
</div>

<div class="overflow-x-auto bg-white rounded">
<table class="min-w-full text-sm">
    <thead class="bg-[#F5EDE3]">
        <tr>
            <th class="p-2 text-left">Nombre</th>
            <th class="p-2 text-left">Email</th>
            <th class="p-2 text-left">Rol</th>
            <th class="p-2">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($usuarios as $u)
        <tr class="border-t">
            <td class="p-2">{{ $u->name }}</td>
            <td class="p-2">{{ $u->email }}</td>
            <td class="p-2">{{ ucfirst($u->role) }}</td>
            <td class="p-2 text-center">
                <a href="{{ route('usuarios.edit',$u) }}" class="text-amber-700 hover:underline">Editar</a> ·
                <form action="{{ route('usuarios.destroy',$u) }}" method="POST" class="inline" onsubmit="return confirm('Eliminar usuario?')">
                    @csrf @method('DELETE')
                    <button class="text-red-700 hover:underline">Eliminar</button>
                </form>
            </td>
        </tr>
        @endforeach
        @if($usuarios->isEmpty())
            <tr><td colspan="4" class="p-4 text-center text-gray-500">Sin registros</td></tr>
        @endif
    </tbody>
</table>
</div>

<div class="mt-4">{{ $usuarios->links() }}</div>
@endsection
