@extends('layouts.app')

@section('content')

@php
    $estadoVehiculo = [
        'Disponible' => ['badge' => 'bg-green-500/20 text-green-400',  'dot' => 'bg-green-400'],
        'Reservado'  => ['badge' => 'bg-yellow-500/20 text-yellow-400','dot' => 'bg-yellow-400'],
        'Vendido'    => ['badge' => 'bg-red-500/20 text-red-400',      'dot' => 'bg-red-400'],
    ];
@endphp

{{-- ===================== VISTA MÓVIL ===================== --}}
<div class="lg:hidden space-y-5">

    {{-- Encabezado --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold">Vehículos</h1>
            <p class="text-gray-400 text-sm mt-0.5">Inventario de vehículos</p>
        </div>
        @can('create', App\Models\Vehiculo::class)
            <a href="{{ route('vehiculos.create') }}"
                class="w-11 h-11 bg-blue-600 hover:bg-blue-700 rounded-2xl flex items-center justify-center transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </a>
        @endcan
    </div>

    {{-- Contadores por estado --}}
    <div class="grid grid-cols-3 gap-3">
        @php
            $disponibles = $vehiculos->where('estado', 'Disponible')->count();
            $reservados  = $vehiculos->where('estado', 'Reservado')->count();
            $vendidosV   = $vehiculos->where('estado', 'Vendido')->count();
        @endphp
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-3 text-center">
            <p class="text-2xl font-bold text-green-400">{{ $disponibles }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Disponibles</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-3 text-center">
            <p class="text-2xl font-bold text-yellow-400">{{ $reservados }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Reservados</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-3 text-center">
            <p class="text-2xl font-bold text-red-400">{{ $vendidosV }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Vendidos</p>
        </div>
    </div>

    {{-- Filtros móvil (acordeón) --}}
    <div x-data="{ open: false }" class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <button @click="open = !open"
            class="w-full flex items-center justify-between px-4 py-3.5 text-sm font-medium text-gray-300 hover:text-white transition">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
                Filtros
                @if(request()->hasAny(['placa','marca','estado','concesionario_id']))
                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                @endif
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>
        <div x-show="open" x-collapse class="border-t border-gray-800">
            <form method="GET" action="{{ route('vehiculos.index') }}" class="p-4 space-y-3">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Placa</label>
                    <input type="text" name="placa" value="{{ request('placa') }}" placeholder="Ej. ABC123"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Marca</label>
                        <select name="marca" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                            <option value="">Todas</option>
                            @foreach($marcas as $marca)
                                <option value="{{ $marca }}" {{ request('marca') == $marca ? 'selected' : '' }}>{{ $marca }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Estado</label>
                        <select name="estado" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                            <option value="">Todos</option>
                            <option value="Disponible" {{ request('estado') == 'Disponible' ? 'selected' : '' }}>Disponible</option>
                            <option value="Reservado"  {{ request('estado') == 'Reservado'  ? 'selected' : '' }}>Reservado</option>
                            <option value="Vendido"    {{ request('estado') == 'Vendido'    ? 'selected' : '' }}>Vendido</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 py-2 rounded-xl text-sm font-medium transition">Aplicar</button>
                    @if(request()->hasAny(['placa','marca','estado','concesionario_id']))
                        <a href="{{ route('vehiculos.index') }}" class="px-4 py-2 bg-gray-800 rounded-xl text-sm text-gray-400 hover:text-white transition">Limpiar</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Lista de vehículos --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-semibold text-gray-200">Inventario</h2>
            <span class="text-xs text-gray-500">{{ $vehiculos->count() }} resultados</span>
        </div>

        <div class="space-y-2">
            @forelse($vehiculos as $vehiculo)
                @php $evcfg = $estadoVehiculo[$vehiculo->estado] ?? ['badge' => 'bg-gray-500/20 text-gray-400']; @endphp
                <a href="{{ route('vehiculos.show', $vehiculo) }}"
                    class="flex items-center gap-3 bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3.5 hover:border-gray-700 transition active:scale-[.99]">
                    @if($vehiculo->fotoUrl)
                        <img src="{{ $vehiculo->fotoUrl }}" alt="Foto de {{ $vehiculo->placa }}"
                            class="w-10 h-10 object-cover rounded-xl shrink-0">
                    @else
                        <div class="w-10 h-10 bg-gray-800 rounded-xl flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold text-sm font-mono">{{ $vehiculo->placa }}</p>
                            <span class="text-xs {{ $evcfg['badge'] }} px-2 py-0.5 rounded-full shrink-0">{{ $vehiculo->estado }}</span>
                        </div>
                        <p class="text-sm text-gray-300 truncate mt-0.5">{{ $vehiculo->marca }} {{ $vehiculo->linea }} {{ $vehiculo->modelo }}</p>
                        <p class="text-xs text-blue-400 font-medium mt-0.5">$ {{ number_format($vehiculo->precio_expocar, 0, ',', '.') }}</p>
                        @if($vehiculo->numero_llave)
                            <p class="text-xs text-gray-500 mt-0.5">Llave: {{ $vehiculo->numero_llave }}</p>
                        @endif
                    </div>
                </a>
            @empty
                <div class="text-center py-12 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-10 h-10 mx-auto mb-3 text-gray-700">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                    No se encontraron vehículos
                </div>
            @endforelse
        </div>
    </div>

</div>

{{-- ===================== VISTA DESKTOP ===================== --}}
<div class="hidden lg:block">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">Vehículos</h1>
            <p class="text-gray-400 mt-1">Inventario de vehículos</p>
        </div>
        @can('create', App\Models\Vehiculo::class)
            @include('partials._boton-crear', ['href' => route('vehiculos.create'), 'texto' => 'Nuevo Vehículo'])
        @endcan
    </div>

    {{-- Panel de filtros desktop --}}
    <form method="GET" action="{{ route('vehiculos.index') }}" class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-6">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-300">Filtros</h2>
            @if(request()->hasAny(['placa','marca','estado','concesionario_id']))
                <a href="{{ route('vehiculos.index') }}" class="text-xs text-gray-400 hover:text-white transition">✕ Limpiar filtros</a>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
            <div>
                <label class="block text-xs text-gray-400 mb-1">Placa</label>
                <input type="text" name="placa" value="{{ request('placa') }}" placeholder="Ej. ABC123"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Marca</label>
                <select name="marca" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                    <option value="">Todas las marcas</option>
                    @foreach($marcas as $marca)
                        <option value="{{ $marca }}" {{ request('marca') == $marca ? 'selected' : '' }}>{{ $marca }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Estado</label>
                <select name="estado" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                    <option value="">Todos los estados</option>
                    <option value="Disponible" {{ request('estado') == 'Disponible' ? 'selected' : '' }}>Disponible</option>
                    <option value="Reservado"  {{ request('estado') == 'Reservado'  ? 'selected' : '' }}>Reservado</option>
                    <option value="Vendido"    {{ request('estado') == 'Vendido'    ? 'selected' : '' }}>Vendido</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Concesionario</label>
                <select name="concesionario_id" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($concesionarios as $c)
                        <option value="{{ $c->id }}" {{ request('concesionario_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-6 py-2 rounded-xl text-sm font-medium transition">Aplicar filtros</button>
        </div>

    </form>

    {{-- Tabla desktop --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-gray-800 flex justify-between items-center">
            <h2 class="text-xl font-semibold">
                Lista de Vehículos
                <span class="text-sm font-normal text-gray-400 ml-2">({{ $vehiculos->count() }} resultados)</span>
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-800 text-gray-400 text-sm uppercase">
                    <tr>
                        <th class="p-4">Foto</th>
                        <th class="p-4">Placa</th>
                        <th class="p-4 hidden md:table-cell">Llave</th>
                        <th class="p-4 hidden sm:table-cell">Marca</th>
                        <th class="p-4 hidden sm:table-cell">Línea</th>
                        <th class="p-4 hidden md:table-cell">Versión</th>
                        <th class="p-4 hidden sm:table-cell">Año</th>
                        <th class="p-4 hidden md:table-cell">Concesionario</th>
                        <th class="p-4 hidden sm:table-cell">Precio Expocar</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehiculos as $vehiculo)
                        @php $evcfg = $estadoVehiculo[$vehiculo->estado] ?? ['badge' => 'bg-gray-500/20 text-gray-400']; @endphp
                        <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition">
                            <td class="p-4">
                                @if($vehiculo->fotoUrl)
                                    <img src="{{ $vehiculo->fotoUrl }}" alt="Foto de {{ $vehiculo->placa }}"
                                        class="w-14 h-10 object-cover rounded-lg">
                                @else
                                    <div class="w-14 h-10 bg-gray-800 rounded-lg"></div>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="font-semibold">{{ $vehiculo->placa }}</div>
                                <div class="sm:hidden text-xs text-gray-400 mt-0.5 space-y-0.5">
                                    <div>{{ $vehiculo->marca }} {{ $vehiculo->linea }}</div>
                                    <div>{{ $vehiculo->modelo }} · $ {{ number_format($vehiculo->precio_expocar, 0, ',', '.') }}</div>
                                </div>
                            </td>
                            <td class="p-4 hidden md:table-cell">{{ $vehiculo->numero_llave ?: '—' }}</td>
                            <td class="p-4 hidden sm:table-cell">{{ $vehiculo->marca }}</td>
                            <td class="p-4 hidden sm:table-cell">{{ $vehiculo->linea }}</td>
                            <td class="p-4 hidden md:table-cell">{{ $vehiculo->version }}</td>
                            <td class="p-4 hidden sm:table-cell">{{ $vehiculo->modelo }}</td>
                            <td class="p-4 hidden md:table-cell">{{ $vehiculo->concesionario?->nombre ?? 'Sin asignar' }}</td>
                            <td class="p-4 hidden sm:table-cell">$ {{ number_format($vehiculo->precio_expocar, 0, ',', '.') }}</td>
                            <td class="p-4">
                                <span class="text-xs {{ $evcfg['badge'] }} px-3 py-1 rounded-full">{{ $vehiculo->estado }}</span>
                            </td>
                            <td class="p-4">
                                @include('partials._acciones', [
                                    'modelo'      => $vehiculo,
                                    'ruta'        => 'vehiculos',
                                    'label'       => 'vehículo',
                                    'sinEditar'   => auth()->user()->cannot('update', $vehiculo),
                                    'sinEliminar' => auth()->user()->cannot('delete', $vehiculo),
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center p-8 text-gray-400">No se encontraron vehículos con los filtros aplicados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
