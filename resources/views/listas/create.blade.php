@extends('layouts.app')
@section('content')
<h1 class="text-xl font-semibold mb-4">Nueva lista de pedido</h1>

<form method="POST" action="{{ route('listas.store') }}" class="bg-white rounded p-4">
    @csrf

    <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Comentario (opcional)</label>
        <input name="comentario" class="border rounded p-2 w-full">
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-[#F5EDE3]">
                <tr>
                    <th class="p-2 text-left">Producto</th>
                    <th class="p-2 text-left">Unidad</th>
                    <th class="p-2 text-left">Exist./Mín.</th>
                    <th class="p-2 text-left">Sugerido</th>
                    <th class="p-2 text-left">Proveedor</th>
                    <th class="p-2 text-left">Precio estimado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bajo as $i => $row)
                <tr class="border-t">
                    <td class="p-2">
                        {{ $row['nombre'] }} {{ $row['presentacion'] ? '· '.$row['presentacion'] : '' }}
                        <input type="hidden" name="items[{{ $i }}][producto]" value="{{ $row['id'] }}">
                    </td>
                    <td class="p-2">{{ $row['unidad'] }}</td>
                    <td class="p-2">{{ $row['exist'] }} / {{ $row['min'] }}</td>
                    <td class="p-2">
                        <input type="number" min="1" class="border rounded p-1 w-24"
                               name="items[{{ $i }}][cantidad]" value="{{ $row['sugerido'] }}">
                    </td>
                    <td class="p-2">
                        <select name="items[{{ $i }}][proveedor]" class="border rounded p-1">
                            <option value="">—</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="p-2">
                        <input type="number" step="0.01" min="0" class="border rounded p-1 w-28"
                               name="items[{{ $i }}][precio]" placeholder="$">
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-4 text-center text-gray-500">No hay productos en bajo stock 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <button class="px-4 py-2 rounded bg-[#6D4C41] text-white">Guardar lista</button>
        <a href="{{ route('listas.index') }}" class="ml-2 px-4 py-2 rounded border">Cancelar</a>
    </div>
</form>
@endsection
