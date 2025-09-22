<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl">Editar proveedor</h2></x-slot>

    <form method="POST" action="{{ route('proveedores.update', $proveedor) }}" class="space-y-3">
        @csrf @method('PUT')

        <div>
            <label>Nombre</label><br>
            <input name="nombre" value="{{ old('nombre', $proveedor->nombre) }}" required>
            @error('nombre') <div class="text-red-600">{{ $message }}</div> @enderror
        </div>

        <div>
            <label>Teléfono</label><br>
            <input name="telefono" value="{{ old('telefono', $proveedor->telefono) }}">
        </div>

        <div>
            <label>Correo</label><br>
            <input name="correo" value="{{ old('correo', $proveedor->correo) }}">
            @error('correo') <div class="text-red-600">{{ $message }}</div> @enderror
        </div>

        <div>
            <label>Dirección</label><br>
            <input name="direccion" value="{{ old('direccion', $proveedor->direccion) }}">
        </div>

        <div>
            <label>Notas</label><br>
            <textarea name="notas">{{ old('notas', $proveedor->notas) }}</textarea>
        </div>

        <button type="submit" class="px-3 py-2 border rounded">Actualizar</button>
        <a href="{{ route('proveedores.index') }}">Cancelar</a>
    </form>
</x-app-layout>
