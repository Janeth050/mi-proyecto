<x-app-layout>
  <x-slot name="header"><h2 class="font-bold text-xl text-[#4E342E]">Dashboard</h2></x-slot>

  <div class="max-w-7xl mx-auto p-4 grid gap-4 md:grid-cols-3">
    <div class="bg-white shadow rounded-lg p-4 border">
      <h3 class="text-[#4E342E] font-semibold mb-2">Existencias bajas</h3>
      @foreach(\App\Models\MateriaPrima::whereColumn('existencias','<=','stock_minimo')->take(5)->get() as $m)
        <div class="flex justify-between py-1 text-sm">
          <span>{{ $m->nombre }}</span>
          <span class="text-red-600 font-semibold">{{ number_format($m->existencias,3) }}</span>
        </div>
      @endforeach
      <a href="{{ route('materias.index') }}" class="text-[#6D4C41] text-sm">Ver todas</a>
    </div>

    <div class="bg-white shadow rounded-lg p-4 border md:col-span-2">
      <h3 class="text-[#4E342E] font-semibold mb-2">Últimos movimientos</h3>
      @foreach(\App\Models\Movimiento::with('materia','usuario')->latest()->take(5)->get() as $mv)
        <div class="py-1 text-sm flex justify-between">
          <span>{{ $mv->created_at->format('d/m H:i') }} — {{ $mv->materia->nombre }}</span>
          <span class="{{ $mv->tipo=='entrada' ? 'text-green-700' : 'text-red-700' }}">
            {{ ucfirst($mv->tipo) }}: {{ number_format($mv->cantidad,3) }}
          </span>
        </div>
      @endforeach
      <a href="{{ route('movimientos.index') }}" class="text-[#6D4C41] text-sm">Ver historial</a>
    </div>
  </div>
</x-app-layout>
