@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('vehiculos.index') }}" class="text-sm text-blue-400 hover:underline">&larr; Volver a Vehículos</a>
        <h1 class="text-2xl lg:text-3xl font-bold mt-2">Catálogos</h1>
        <p class="text-gray-400 text-sm mt-0.5">Valores disponibles para seleccionar en los formularios</p>
    </div>

    <div class="flex gap-2 mb-6 border-b border-gray-800 overflow-x-auto">
        @foreach($tipos as $key => $nombre)
            <a href="{{ route('catalogos.index', $key) }}"
                class="px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 transition
                    {{ $tipo === $key ? 'border-blue-500 text-blue-400' : 'border-transparent text-gray-400 hover:text-gray-200' }}">
                {{ $nombre }}
            </a>
        @endforeach
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/50 rounded-xl p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-500/10 border border-red-500/50 rounded-xl p-4">
            <ul class="text-red-400 text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('catalogos.store', $tipo) }}"
        class="bg-gray-900 border border-gray-800 rounded-3xl p-5 mb-6 flex gap-3">
        @csrf
        <input type="text" name="valor" value="{{ old('valor') }}" placeholder="Nuevo valor para {{ strtolower($etiqueta) }}"
            class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500">
        <button class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl shrink-0">
            Agregar
        </button>
    </form>

    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden">
        <div class="p-5 border-b border-gray-800">
            <h2 class="text-lg font-semibold">
                {{ $etiqueta }}
                <span class="text-sm font-normal text-gray-400 ml-2">({{ $items->count() }})</span>
            </h2>
        </div>
        <div class="divide-y divide-gray-800">
            @forelse($items as $item)
                <div class="p-4 flex items-center justify-between">
                    <span>{{ $item->valor }}</span>
                    <form action="{{ route('catalogos.destroy', $item) }}" method="POST"
                        onsubmit="return confirm('¿Eliminar {{ $item->valor }}? Los vehículos que ya lo usan no se ven afectados.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs transition">
                            Eliminar
                        </button>
                    </form>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    No hay valores registrados todavía
                </div>
            @endforelse
        </div>
    </div>

</div>

@endsection
