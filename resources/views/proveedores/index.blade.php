<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl">Proveedores</h2>
    </x-slot>

    @if(session('ok'))
        <div class="p-3 bg-green-100 border rounded mb-3">{{ session('ok') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('proveedores.create') }}" class="px-3 py-2 border rounded">Nuevo proveedor</a>
    </div>

    <table class="w-full border-collapse">
        <thead>
        <tr>
            <th class="border p-2 text-left">Nombre</th>
            <th class="border p-2">Teléfono</th>
            <th class="border p-2">Correo</th>
            <th class="border p-2">Acciones</th>
        </tr>
        </thead>
        <tbody>
        @foreach($proveedores as $p)
            <tr>
                <td class="border p-2 text-left">{{ $p->nombre }}</td>
                <td class="border p-2">{{ $p->telefono }}</td>
                <td class="border p-2">{{ $p->correo }}</td>
                <td class="border p-2">
                    <a href="{{ route('proveedores.edit',$p) }}">Editar</a>
                    <form action="{{ route('proveedores.destroy',$p) }}" method="POST" style="display:inline"
                          onsubmit="return confirm('¿Eliminar proveedor?')">
                        @csrf @method('DELETE')
                        <button type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-3">
        {{ $proveedores->links() }}
    </div>
</x-app-layout>
