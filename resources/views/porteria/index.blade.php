@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto" x-data="liveSearch()">

    <div class="mb-6">
        <h1 class="text-2xl lg:text-3xl font-bold">Portería — Check-in de vehículos</h1>
        <p class="text-gray-400 text-sm mt-1">Busca por placa para aprobar el ingreso a la feria</p>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-500/10 border border-green-500/30 rounded-2xl px-4 py-3 text-green-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-500/10 border border-red-500/30 rounded-2xl px-4 py-3 text-red-400 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3 mb-4 flex items-center justify-between">
        <span class="text-sm text-gray-400">Ingresados (dentro del área)</span>
        <span class="text-xl font-bold text-blue-400">{{ $totalIngresados }} / {{ $totalDentro }}</span>
    </div>

    <input type="text" x-model="q" placeholder="Buscar por placa, marca o línea..."
        class="w-full bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3 text-sm mb-4 focus:outline-none focus:border-blue-500">

    <div class="space-y-2">
        @forelse($vehiculos as $vehiculo)
            @php
                $dentro = $vehiculo->ubicacion === 'Dentro del área';
                $ingresado = $vehiculo->ingresado_at !== null;
            @endphp
            <div x-show="matches($el)"
                data-search="{{ mb_strtolower($vehiculo->placa.' '.$vehiculo->marca.' '.$vehiculo->linea) }}"
                class="bg-gray-900 border {{ $ingresado ? 'border-green-500/40' : 'border-gray-800' }} rounded-2xl px-4 py-3.5">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-mono font-bold text-lg">{{ $vehiculo->placa }}</p>
                        <p class="text-sm text-gray-400 truncate">{{ $vehiculo->marca }} {{ $vehiculo->linea }} · {{ $vehiculo->concesionario?->nombre ?: 'Sin concesionario' }}</p>
                    </div>
                    <span class="text-xs {{ $dentro ? 'bg-blue-500/20 text-blue-400' : 'bg-gray-700 text-gray-300' }} px-2.5 py-1 rounded-full shrink-0">
                        {{ $vehiculo->ubicacion ?: 'Sin ubicación' }}
                    </span>
                </div>

                <div class="mt-3 pt-3 border-t border-gray-800">
                    @if(!$dentro)
                        <p class="text-sm text-red-400 font-medium">
                            Este vehículo NO está inscrito dentro del área — no se puede aprobar el ingreso aquí.
                        </p>
                    @elseif($ingresado)
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm text-green-400 font-medium">
                                ✓ Ingresó a las {{ $vehiculo->ingresado_at->format('H:i') }}
                            </p>
                            <form action="{{ route('porteria.quitar-ingreso', $vehiculo) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-gray-400 hover:text-red-400 underline transition">
                                    Deshacer
                                </button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('porteria.ingreso', $vehiculo) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 py-2.5 rounded-xl text-sm font-medium transition">
                                Marcar ingreso
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-500 text-sm">
                No hay vehículos registrados.
            </div>
        @endforelse
        <div x-show="q.trim() !== '' && visibleCount === 0" class="text-center py-12 text-gray-500 text-sm">
            Sin resultados para tu búsqueda
        </div>
    </div>

</div>

@endsection
