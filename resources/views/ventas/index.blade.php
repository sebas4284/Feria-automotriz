@extends('layouts.app')

@section('content')

<div x-data="liveSearch()">

{{-- ===================== VISTA MÓVIL ===================== --}}
<div class="lg:hidden space-y-5">

    {{-- Encabezado --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold">Ventas</h1>
            <p class="text-gray-400 text-sm mt-0.5">Gestión de ventas realizadas</p>
        </div>
        <a href="{{ route('ventas.create') }}"
            class="w-11 h-11 bg-blue-600 hover:bg-blue-700 rounded-2xl flex items-center justify-center transition shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-2xl px-4 py-3 text-green-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Resumen del mes --}}
    @php
        $totalMes   = $ventas->filter(fn($v) => $v->fecha_venta?->isCurrentMonth())->count();
        $ingresosMes = $ventas->filter(fn($v) => $v->fecha_venta?->isCurrentMonth())->sum('valor');
        $totalIngresosAll = $ventas->sum('valor');
    @endphp

    <div class="grid grid-cols-2 gap-3">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <div class="w-9 h-9 bg-emerald-600/20 rounded-xl flex items-center justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-emerald-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.854-1.106-2.24 0-3.093 1.147-.884 2.992-.884 4.14 0l.235.18M12 3v18" />
                </svg>
            </div>
            <p class="text-2xl font-bold text-emerald-400">$ {{ number_format($ingresosMes, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Ingresos este mes</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <div class="w-9 h-9 bg-blue-600/20 rounded-xl flex items-center justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                </svg>
            </div>
            <p class="text-2xl font-bold">{{ $ventas->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Ventas totales</p>
        </div>
    </div>

    {{-- Ingreso total --}}
    <div class="bg-gradient-to-r from-blue-900/30 to-indigo-900/30 border border-blue-700/20 rounded-2xl px-4 py-3.5 flex items-center justify-between">
        <div>
            <p class="text-xs text-blue-400 font-medium uppercase tracking-wider">Total acumulado</p>
            <p class="text-xl font-bold text-white mt-0.5">$ {{ number_format($totalIngresosAll, 0, ',', '.') }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500">{{ $ventas->count() }} ventas</p>
            <p class="text-xs text-blue-400 mt-0.5">{{ $totalMes }} este mes</p>
        </div>
    </div>

    {{-- Lista de ventas --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-semibold text-gray-200">Historial</h2>
        </div>

        <input type="text" x-model="q" placeholder="Buscar por comprador o vehículo..."
            class="w-full bg-gray-900 border border-gray-800 rounded-2xl px-4 py-2.5 text-sm mb-3 focus:outline-none focus:border-blue-500">

        <div class="space-y-2">
            @forelse($ventas as $venta)
                <div x-show="matches($el)" data-search="{{ mb_strtolower(($venta->comprador->nombre ?? '').' '.$venta->vehiculo->marca.' '.$venta->vehiculo->modelo.' '.$venta->vehiculo->placa.' '.($venta->concesionarioVende->nombre ?? '').' '.($venta->asesorComercial->nombre ?? '')) }}"
                    class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3.5 hover:border-gray-700 transition">
                    <a href="{{ route('ventas.show', $venta) }}" class="block">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-8 h-8 bg-emerald-600/20 rounded-xl flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-emerald-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                                <p class="font-medium text-sm truncate">{{ $venta->comprador->nombre ?? '—' }}</p>
                            </div>
                            <p class="text-emerald-400 font-bold text-sm shrink-0">$ {{ number_format($venta->valor, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center justify-between pl-10">
                            <p class="text-xs text-gray-400">{{ $venta->vehiculo->placa }} · {{ $venta->vehiculo->marca }} {{ $venta->vehiculo->modelo }}</p>
                            <div class="flex items-center gap-2">
                                @if($venta->forma_pago)
                                    <span class="text-xs bg-gray-800 text-gray-400 px-2 py-0.5 rounded-full">{{ $venta->forma_pago }}</span>
                                @endif
                                @if($venta->tiene_retoma)
                                    <span class="text-xs bg-purple-500/20 text-purple-400 px-2 py-0.5 rounded-full">+ Retoma</span>
                                @endif
                                <p class="text-xs text-gray-600">{{ $venta->fecha_venta?->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 pl-10 mt-2 text-xs text-gray-500">
                            <p class="truncate">Concesionario: <span class="text-gray-300">{{ $venta->concesionarioVende->nombre ?? '—' }}</span></p>
                            <p class="truncate">Asesor: <span class="text-gray-300">{{ $venta->asesorComercial->nombre ?? '—' }}</span></p>
                        </div>
                    </a>
                    <div class="flex gap-2 pt-3 mt-3 border-t border-gray-800">
                        @can('update', $venta)
                            <a href="{{ route('ventas.edit', $venta) }}"
                                class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-xl bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 text-xs transition">
                                Editar
                            </a>
                        @endcan
                        @can('delete', $venta)
                            @include('ventas._eliminar_boton', [
                                'venta' => $venta,
                                'clase' => 'flex-1 flex items-center justify-center gap-1.5 px-4 py-1.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs transition',
                                'contenido' => 'Eliminar',
                            ])
                        @endcan
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-10 h-10 mx-auto mb-3 text-gray-700">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                    </svg>
                    No hay ventas registradas
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

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Ventas</h1>
            <p class="text-gray-400">Gestión de ventas realizadas</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('ventas.eliminadas') }}"
                    class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2.5 rounded-xl transition text-sm font-medium">
                    Ventas eliminadas
                </a>
            @endif
            @include('partials._boton-crear', ['href' => route('ventas.create'), 'texto' => 'Nueva Venta'])
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <p class="text-2xl font-bold text-emerald-400">$ {{ number_format($ingresosMes, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Ingresos este mes</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <p class="text-2xl font-bold">{{ $ventas->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Ventas totales</p>
        </div>
        <div class="bg-gradient-to-r from-blue-900/30 to-indigo-900/30 border border-blue-700/20 rounded-2xl p-4">
            <p class="text-2xl font-bold text-white">$ {{ number_format($totalIngresosAll, 0, ',', '.') }}</p>
            <p class="text-xs text-blue-400 mt-1">Total acumulado · {{ $totalMes }} este mes</p>
        </div>
    </div>

    <div class="bg-gray-900 rounded-3xl overflow-hidden border border-gray-800">
        <div class="p-5 border-b border-gray-800 flex justify-between items-center">
            <h2 class="text-xl font-semibold">
                Historial de Ventas
                <span class="text-sm font-normal text-gray-400 ml-2">({{ $ventas->count() }} resultados)</span>
            </h2>
            <input type="text" x-model="q" placeholder="Buscar por comprador o vehículo..."
                class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm w-72 focus:outline-none focus:border-blue-500">
        </div>
        <table class="w-full">
            <thead class="bg-gray-800">
                <tr>
                    <th class="p-4 text-left">Comprador</th>
                    <th class="p-4 text-left">Placa</th>
                    <th class="p-4 text-left">Vehículo</th>
                    <th class="p-4 text-left hidden lg:table-cell">Concesionario</th>
                    <th class="p-4 text-left hidden xl:table-cell">Asesor</th>
                    <th class="p-4 text-left">Valor</th>
                    <th class="p-4 text-left">Fecha</th>
                    <th class="p-4 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventas as $venta)
                    <tr x-show="matches($el)" data-search="{{ mb_strtolower(($venta->comprador->nombre ?? '').' '.$venta->vehiculo->marca.' '.$venta->vehiculo->modelo.' '.$venta->vehiculo->placa.' '.($venta->concesionarioVende->nombre ?? '').' '.($venta->asesorComercial->nombre ?? '')) }}"
                        class="border-t border-gray-800 hover:bg-gray-800/40 transition">
                        <td class="p-4">{{ $venta->comprador->nombre ?? '—' }}</td>
                        <td class="p-4 font-mono">{{ $venta->vehiculo->placa }}</td>
                        <td class="p-4">{{ $venta->vehiculo->marca }} {{ $venta->vehiculo->modelo }}</td>
                        <td class="p-4 hidden lg:table-cell">{{ $venta->concesionarioVende->nombre ?? '—' }}</td>
                        <td class="p-4 hidden xl:table-cell">{{ $venta->asesorComercial->nombre ?? '—' }}</td>
                        <td class="p-4 text-emerald-400 font-bold">$ {{ number_format($venta->valor, 0, ',', '.') }}</td>
                        <td class="p-4 text-gray-400">{{ $venta->fecha_venta?->format('d/m/Y') }}</td>
                        <td class="p-4">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('ventas.show', $venta) }}" title="Ver"
                                    class="p-1.5 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-300 hover:text-white transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </a>
                                @can('update', $venta)
                                    <a href="{{ route('ventas.edit', $venta) }}" title="Editar"
                                        class="p-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 hover:text-blue-300 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                        </svg>
                                    </a>
                                @endcan
                                @can('delete', $venta)
                                    @include('ventas._eliminar_boton', [
                                        'venta' => $venta,
                                        'clase' => 'p-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-400 hover:text-red-300 transition',
                                        'contenido' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>',
                                    ])
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center p-8 text-gray-400">No hay ventas registradas</td>
                    </tr>
                @endforelse
                <tr x-show="q.trim() !== '' && visibleCount === 0">
                    <td colspan="8" class="text-center p-8 text-gray-400">Sin resultados para tu búsqueda</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

</div>

@endsection
