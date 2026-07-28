@extends('layouts.app')

@section('content')

@php
    $estadoVehiculo = [
        'Disponible' => ['badge' => 'bg-green-500/20 text-green-400',  'dot' => 'bg-green-400'],
        'Reservado'  => ['badge' => 'bg-yellow-500/20 text-yellow-400','dot' => 'bg-yellow-400'],
        'Vendido'    => ['badge' => 'bg-red-500/20 text-red-400',      'dot' => 'bg-red-400'],
    ];
@endphp

<div x-data="liveSearch('{{ addslashes(request('placa', '')) }}')">

@if(session('success'))
    <div class="mb-4 bg-green-500/10 border border-green-500/30 rounded-2xl px-4 py-3 text-green-400 text-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('warning'))
    <div class="mb-4 bg-amber-500/10 border border-amber-500/30 rounded-2xl px-4 py-3 text-amber-400 text-sm">
        {{ session('warning') }}
    </div>
@endif

{{-- ===================== VISTA MÓVIL ===================== --}}
<div class="lg:hidden space-y-5">

    {{-- Encabezado --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold">Vehículos</h1>
            <p class="text-gray-400 text-sm mt-0.5">Inventario de vehículos</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('catalogos.index', 'marca') }}"
                    class="w-11 h-11 bg-gray-800 hover:bg-gray-700 rounded-2xl flex items-center justify-center transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                    </svg>
                </a>
            @endif
            @can('create', App\Models\Vehiculo::class)
                <a href="{{ route('vehiculos.create') }}"
                    class="w-11 h-11 bg-blue-600 hover:bg-blue-700 rounded-2xl flex items-center justify-center transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </a>
            @endcan
        </div>
    </div>

    @if($concesionarioCupo)
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <p class="text-xs text-gray-500 mb-1">Cupo feria — {{ $concesionarioCupo->nombre }}</p>
            @if($concesionarioCupo->cupo_feria !== null)
                <p class="text-lg font-bold {{ $cupoUsadoActual >= $concesionarioCupo->cupo_feria ? 'text-red-400' : 'text-white' }}">
                    {{ $cupoUsadoActual }} / {{ $concesionarioCupo->cupo_feria }} cupos usados
                </p>
            @else
                <p class="text-lg font-bold text-white">{{ $cupoUsadoActual }} cupos usados (sin límite configurado)</p>
            @endif
        </div>
    @endif

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
                @if(request()->hasAny(['placa','marca','estado','ubicacion','concesionario_id']))
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
                    <label class="block text-xs text-gray-400 mb-1">Buscar (placa)</label>
                    <input type="text" name="placa" x-model="q" placeholder="Ej. ABC123"
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
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Ubicación</label>
                        <select name="ubicacion" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                            <option value="">Todas</option>
                            <option value="Dentro del área" {{ request('ubicacion') == 'Dentro del área' ? 'selected' : '' }}>Dentro del área</option>
                            <option value="Fuera del área"  {{ request('ubicacion') == 'Fuera del área'  ? 'selected' : '' }}>Fuera del área</option>
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
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 py-2 rounded-xl text-sm font-medium transition">Aplicar</button>
                    @if(request()->hasAny(['placa','marca','estado','ubicacion','concesionario_id']))
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
                <div x-show="matches($el)"
                    data-search="{{ mb_strtolower($vehiculo->placa.' '.$vehiculo->marca.' '.$vehiculo->linea.' '.$vehiculo->version.' '.$vehiculo->numero_llave) }}"
                    class="flex items-center gap-2 bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3.5 hover:border-gray-700 transition">
                    <a href="{{ route('vehiculos.show', $vehiculo) }}"
                        class="flex items-center gap-3 flex-1 min-w-0 active:scale-[.99]">
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
                    @can('update', $vehiculo)
                        <a href="{{ route('vehiculos.edit', $vehiculo) }}" title="Editar"
                            class="p-2.5 rounded-lg bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 hover:text-blue-300 transition shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                            </svg>
                        </a>
                    @endcan
                </div>
            @empty
                <div class="text-center py-12 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-10 h-10 mx-auto mb-3 text-gray-700">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                    No se encontraron vehículos
                </div>
            @endforelse
            <div x-show="q.trim() !== '' && visibleCount === 0" class="text-center py-12 text-gray-500">
                Sin resultados para tu búsqueda
            </div>
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
        <div class="flex items-center gap-3">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('catalogos.index', 'marca') }}"
                    class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2.5 rounded-xl transition text-sm font-medium">
                    Gestionar catálogos
                </a>
            @endif
            @can('create', App\Models\Vehiculo::class)
                @include('partials._boton-crear', ['href' => route('vehiculos.create'), 'texto' => 'Nuevo Vehículo'])
            @endcan
        </div>
    </div>

    {{-- Panel de filtros desktop --}}
    <form method="GET" action="{{ route('vehiculos.index') }}" class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-6">

        <div class="flex flex-wrap items-end gap-3">
            <div class="w-40">
                <label class="block text-xs text-gray-400 mb-1">Buscar</label>
                <input type="text" name="placa" x-model="q" placeholder="Placa, marca..."
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div class="w-36">
                <label class="block text-xs text-gray-400 mb-1">Marca</label>
                <select name="marca" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500">
                    <option value="">Todas</option>
                    @foreach($marcas as $marca)
                        <option value="{{ $marca }}" {{ request('marca') == $marca ? 'selected' : '' }}>{{ $marca }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs text-gray-400 mb-1">Estado</label>
                <select name="estado" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500">
                    <option value="">Todos</option>
                    <option value="Disponible" {{ request('estado') == 'Disponible' ? 'selected' : '' }}>Disponible</option>
                    <option value="Reservado"  {{ request('estado') == 'Reservado'  ? 'selected' : '' }}>Reservado</option>
                    <option value="Vendido"    {{ request('estado') == 'Vendido'    ? 'selected' : '' }}>Vendido</option>
                </select>
            </div>
            <div class="w-40">
                <label class="block text-xs text-gray-400 mb-1">Ubicación</label>
                <select name="ubicacion" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500">
                    <option value="">Todas</option>
                    <option value="Dentro del área" {{ request('ubicacion') == 'Dentro del área' ? 'selected' : '' }}>Dentro del área</option>
                    <option value="Fuera del área"  {{ request('ubicacion') == 'Fuera del área'  ? 'selected' : '' }}>Fuera del área</option>
                </select>
            </div>
            <div class="w-44">
                <label class="block text-xs text-gray-400 mb-1">Concesionario</label>
                <select name="concesionario_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($concesionarios as $c)
                        <option value="{{ $c->id }}" {{ request('concesionario_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-5 py-1.5 rounded-lg text-sm font-medium transition">Aplicar</button>

            @if(request()->hasAny(['placa','marca','estado','ubicacion','concesionario_id']))
                <a href="{{ route('vehiculos.index') }}" class="text-xs text-gray-400 hover:text-white transition px-1 py-1.5">✕ Limpiar</a>
            @endif
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
                        <th class="p-4 hidden lg:table-cell">Ubicación</th>
                        <th class="p-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehiculos as $vehiculo)
                        @php $evcfg = $estadoVehiculo[$vehiculo->estado] ?? ['badge' => 'bg-gray-500/20 text-gray-400']; @endphp
                        <tr x-show="matches($el)" data-search="{{ mb_strtolower($vehiculo->placa.' '.$vehiculo->marca.' '.$vehiculo->linea.' '.$vehiculo->version.' '.$vehiculo->numero_llave) }}"
                            class="border-b border-gray-800 hover:bg-gray-800/50 transition">
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
                            <td class="p-4 hidden lg:table-cell">
                                <span class="text-xs {{ $vehiculo->ubicacion == 'Dentro del área' ? 'bg-blue-500/20 text-blue-400' : 'bg-gray-700 text-gray-300' }} px-3 py-1 rounded-full whitespace-nowrap">
                                    {{ $vehiculo->ubicacion }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('vehiculos.ficha', $vehiculo) }}" target="_blank"
                                        title="Ficha / PDF"
                                        class="p-2.5 sm:p-1.5 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-300 hover:text-white transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3 16.5h1.5m-1.5-3h1.5m-6-3h6m-6 3h1.5m-1.5 3h1.5M9 3.75H6.75A2.25 2.25 0 0 0 4.5 6v12a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25V11.25a9 9 0 0 0-9-7.5Z" />
                                        </svg>
                                    </a>
                                    @include('partials._acciones', [
                                        'modelo'      => $vehiculo,
                                        'ruta'        => 'vehiculos',
                                        'label'       => 'vehículo',
                                        'sinEditar'   => auth()->user()->cannot('update', $vehiculo),
                                        'sinEliminar' => auth()->user()->cannot('delete', $vehiculo),
                                    ])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center p-8 text-gray-400">No se encontraron vehículos con los filtros aplicados</td>
                        </tr>
                    @endforelse
                    <tr x-show="q.trim() !== '' && visibleCount === 0">
                        <td colspan="12" class="text-center p-8 text-gray-400">Sin resultados para tu búsqueda</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

</div>

@endsection
