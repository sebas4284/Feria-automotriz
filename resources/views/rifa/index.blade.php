@extends('layouts.app')

@section('content')

<div x-data="liveSearch()">

{{-- ===================== VISTA MÓVIL ===================== --}}
<div class="lg:hidden space-y-5">

    <div>
        <h1 class="text-2xl font-bold">Rifa / Experiencia</h1>
        <p class="text-gray-400 text-sm mt-0.5">Participantes registrados para el sorteo</p>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-amber-600/20 rounded-xl flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                </svg>
            </div>
            <span class="text-sm text-gray-300">Total participantes</span>
        </div>
        <span class="text-xl font-bold text-amber-400">{{ $ventas->count() }}</span>
    </div>

    <input type="text" x-model="q" placeholder="Buscar por comprador, boleta, cédula o teléfono..."
        class="w-full bg-gray-900 border border-gray-800 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">

    <div class="space-y-2">
        @forelse($ventas as $venta)
            <div x-show="matches($el)"
                data-search="{{ mb_strtolower(($venta->comprador->nombre ?? '').' '.$venta->boleta.' '.($venta->comprador->identificacion ?? '').' '.($venta->comprador->telefono ?? '').' '.($venta->vehiculo->marca ?? '').' '.($venta->vehiculo->modelo ?? '')) }}"
                class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3.5">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <p class="font-semibold text-sm truncate">{{ $venta->comprador->nombre ?? '—' }}</p>
                    <span class="text-xs bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full shrink-0">{{ $venta->boleta }}</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs text-gray-400">
                    <div>
                        <p class="text-gray-600">Cédula</p>
                        <p>{{ $venta->comprador->identificacion ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Teléfono</p>
                        <p>{{ $venta->comprador->telefono ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Vehículo</p>
                        <p>{{ $venta->vehiculo->marca ?? '' }} {{ $venta->vehiculo->modelo ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Fecha</p>
                        <p>{{ $venta->fecha_venta?->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-500">
                No hay participantes registrados
            </div>
        @endforelse
        <div x-show="q.trim() !== '' && visibleCount === 0" class="text-center py-12 text-gray-500">
            Sin resultados para tu búsqueda
        </div>
    </div>

</div>

{{-- ===================== VISTA DESKTOP ===================== --}}
<div class="hidden lg:block">

    <div class="mb-6">
        <h1 class="text-3xl font-bold">Rifa / Experiencia</h1>
        <p class="text-gray-400">Participantes registrados para el sorteo ({{ $ventas->count() }} boletas)</p>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3 flex items-center justify-between mb-6 max-w-xs">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-amber-600/20 rounded-xl flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                </svg>
            </div>
            <span class="text-sm text-gray-300">Total participantes</span>
        </div>
        <span class="text-xl font-bold text-amber-400">{{ $ventas->count() }}</span>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden">
        <div class="p-5 border-b border-gray-800 flex justify-between items-center">
            <h2 class="text-xl font-semibold">
                Participantes
                <span class="text-sm font-normal text-gray-400 ml-2">({{ $ventas->count() }} boletas)</span>
            </h2>
            <input type="text" x-model="q" placeholder="Buscar por comprador, boleta, cédula o teléfono..."
                class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm w-72 focus:outline-none focus:border-blue-500">
        </div>
        <table class="w-full">
            <thead class="bg-gray-800">
                <tr>
                    <th class="p-4 text-left">Boleta</th>
                    <th class="p-4 text-left">Comprador</th>
                    <th class="p-4 text-left hidden sm:table-cell">Cédula</th>
                    <th class="p-4 text-left hidden sm:table-cell">Teléfono</th>
                    <th class="p-4 text-left hidden md:table-cell">Vehículo</th>
                    <th class="p-4 text-left">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventas as $venta)
                    <tr x-show="matches($el)"
                        data-search="{{ mb_strtolower(($venta->comprador->nombre ?? '').' '.$venta->boleta.' '.($venta->comprador->identificacion ?? '').' '.($venta->comprador->telefono ?? '').' '.($venta->vehiculo->marca ?? '').' '.($venta->vehiculo->modelo ?? '')) }}"
                        class="border-t border-gray-800 hover:bg-gray-800/40 transition">
                        <td class="p-4">
                            <span class="text-xs bg-amber-500/20 text-amber-400 px-3 py-1 rounded-full font-medium">{{ $venta->boleta }}</span>
                        </td>
                        <td class="p-4">{{ $venta->comprador->nombre ?? '—' }}</td>
                        <td class="p-4 hidden sm:table-cell">{{ $venta->comprador->identificacion ?? '—' }}</td>
                        <td class="p-4 hidden sm:table-cell">{{ $venta->comprador->telefono ?? '—' }}</td>
                        <td class="p-4 hidden md:table-cell">{{ $venta->vehiculo->marca ?? '' }} {{ $venta->vehiculo->modelo ?? '' }}</td>
                        <td class="p-4">{{ $venta->fecha_venta?->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">No hay participantes registrados</td>
                    </tr>
                @endforelse
                <tr x-show="q.trim() !== '' && visibleCount === 0">
                    <td colspan="6" class="p-8 text-center text-gray-500">Sin resultados para tu búsqueda</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

</div>

@endsection
