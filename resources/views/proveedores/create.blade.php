<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl">Nuevo proveedor</h2></x-slot>

    <form method="POST" action="{{ route('proveedores.store') }}" class="space-y-3">
        @csrf
        <div>
            <label>Nombre</label><br>
            <input name="nombre" value="{{ old('nombre') }}" required>
            @error('nombre') <div class="text-red-600">{{ $message }}</div> @enderror
        </div>

        <div>
            <label>Teléfono</label><br>
            <input name="telefono" value="{{ old('telefono') }}">
        </div>

        <div>
            <label>Correo</label><br>
            <input name="correo" value="{{ old('correo') }}">
            @error('correo') <div class="text-red-600">{{ $message }}</div> @enderror
        </div>

        <div>
            <label>Dirección</label><br>
            <input name="direccion" value="{{ old('direccion') }}">
        </div>

        <div>
            <label>Notas</label><br>
            <textarea name="notas">{{ old('notas') }}</textarea>
        </div>

        <button type="submit" class="px-3 py-2 border rounded">Guardar</button>
        <a href="{{ route('proveedores.index') }}">Cancelar</a>
    </form>
</x-app-layout>
