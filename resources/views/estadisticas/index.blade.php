@extends('layouts.app')

@section('content')

@php
    $estadoConfig = [
        'Nuevo'       => ['badge' => 'bg-blue-500/20 text-blue-400',    'label' => 'Nuevo'],
        'Asignado'    => ['badge' => 'bg-purple-500/20 text-purple-400', 'label' => 'Asignado'],
        'Contactado'  => ['badge' => 'bg-teal-500/20 text-teal-400',    'label' => 'Contactado'],
        'Negociacion' => ['badge' => 'bg-amber-500/20 text-amber-400',  'label' => 'Negociación'],
        'Vendido'     => ['badge' => 'bg-green-500/20 text-green-400',  'label' => 'Vendido'],
        'Perdido'     => ['badge' => 'bg-red-500/20 text-red-400',      'label' => 'Perdido'],
    ];
@endphp

<div>

    <div class="mb-6">
        <h1 class="text-2xl lg:text-3xl font-bold">Estadísticas</h1>
        <p class="text-gray-400 mt-1 text-sm">
            {{ $isAdmin ? 'Resumen general de todo el evento' : 'Resumen de tu concesionario' }}
        </p>
    </div>

    {{-- KPIs generales --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 lg:p-5">
            <p class="text-gray-400 text-xs lg:text-sm font-medium mb-1">Ventas</p>
            <p class="text-2xl lg:text-3xl font-bold">{{ $totalVentas }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 lg:p-5">
            <p class="text-gray-400 text-xs lg:text-sm font-medium mb-1">Ingresos</p>
            <p class="text-xl lg:text-2xl font-bold text-emerald-400">$ {{ number_format($totalIngresos, 0, ',', '.') }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 lg:p-5">
            <p class="text-gray-400 text-xs lg:text-sm font-medium mb-1">Clientes</p>
            <p class="text-2xl lg:text-3xl font-bold">{{ $totalClientes }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 lg:p-5">
            <p class="text-gray-400 text-xs lg:text-sm font-medium mb-1">Leads</p>
            <p class="text-2xl lg:text-3xl font-bold text-blue-400">{{ $totalLeads }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 lg:p-5">
            <p class="text-gray-400 text-xs lg:text-sm font-medium mb-1">Rifa</p>
            <p class="text-2xl lg:text-3xl font-bold text-amber-400">{{ $totalRifa }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 {{ $isAdmin ? 'lg:grid-cols-2' : '' }} gap-6">

        {{-- Leads por estado --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-xl font-semibold mb-1">Leads por estado de gestión</h2>
            <p class="text-gray-500 text-xs mb-5">"Vendido" se usa como aproximación de leads convertidos en venta</p>

            <div class="space-y-3">
                @foreach(['Nuevo', 'Asignado', 'Contactado', 'Negociacion', 'Vendido', 'Perdido'] as $estado)
                    @php $total = $leadsPorEstado[$estado] ?? 0; @endphp
                    <div class="flex items-center justify-between">
                        <span class="text-xs {{ $estadoConfig[$estado]['badge'] }} px-3 py-1 rounded-full">{{ $estadoConfig[$estado]['label'] }}</span>
                        <span class="font-bold">{{ $total }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 pt-5 border-t border-gray-800 flex items-center justify-between">
                <p class="text-sm text-gray-400">Leads convertidos (Vendido)</p>
                <p class="text-lg font-bold text-green-400">{{ $leadsVendidos }}</p>
            </div>
        </div>

        {{-- Ventas por concesionario (solo admin) --}}
        @if($isAdmin)
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h2 class="text-xl font-semibold mb-5">Ventas por concesionario</h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-gray-400 uppercase text-xs">
                            <tr>
                                <th class="pb-3">Concesionario</th>
                                <th class="pb-3">Ventas</th>
                                <th class="pb-3">Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventasPorConcesionario as $fila)
                                <tr class="border-t border-gray-800">
                                    <td class="py-3">{{ $fila->concesionarioVende->nombre ?? 'Sin asignar' }}</td>
                                    <td class="py-3">{{ $fila->total_ventas }}</td>
                                    <td class="py-3 text-emerald-400 font-medium">$ {{ number_format($fila->total_ingresos, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-gray-500">Sin ventas registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

</div>

@include('partials._auto-refresh')
@endsection
