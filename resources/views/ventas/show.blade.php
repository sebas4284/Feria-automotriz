@extends('layouts.app')

@section('content')

@php
    $formaPagoLabel = [
        'Contado' => 'Contado',
        'Credito' => 'Crédito',
        'Credito y Contado' => 'Crédito y Contado',
    ][$venta->forma_pago] ?? $venta->forma_pago;
@endphp

<div class="max-w-4xl mx-auto">

    <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold">Venta #{{ $venta->id }}</h1>
            <p class="text-gray-400 mt-1 text-sm">Detalle de la venta</p>
        </div>
        <div class="flex gap-2 shrink-0">
            @can('update', $venta)
                <a href="{{ route('ventas.edit', $venta) }}"
                    class="bg-blue-600 hover:bg-blue-700 px-4 py-2.5 rounded-xl transition font-medium text-sm">
                    Editar
                </a>
            @endcan
            @can('delete', $venta)
                @include('ventas._eliminar_boton', [
                    'venta' => $venta,
                    'clase' => 'bg-red-500/20 hover:bg-red-500/40 text-red-400 px-4 py-2.5 rounded-xl transition font-medium text-sm',
                    'contenido' => 'Eliminar',
                ])
            @endcan
            <a href="{{ route('ventas.index') }}"
                class="bg-gray-800 hover:bg-gray-700 px-4 py-2.5 rounded-xl transition text-sm">
                Volver
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/50 rounded-xl p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if($venta->participa_experiencia)
        <div class="mb-6 bg-amber-500/10 border border-amber-500/50 rounded-2xl p-6 text-center">
            <p class="text-amber-400 text-sm mb-1">Boleta de la experiencia</p>
            <p class="text-3xl font-bold text-amber-300 tracking-widest">{{ $venta->boleta }}</p>
            @if($venta->detalle_experiencia)
                <p class="text-sm text-amber-200 mt-2">{{ $venta->detalle_experiencia }}</p>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-xl font-semibold mb-6">Comprador</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-gray-400 text-sm">Nombre</p>
                    <p class="text-white font-medium text-lg">{{ $venta->comprador->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Identificación</p>
                    <p class="text-white">{{ $venta->comprador->identificacion ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Teléfono</p>
                    <p class="text-white">{{ $venta->comprador->telefono ?? 'No registrado' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Dirección</p>
                    <p class="text-white">{{ $venta->comprador->direccion ?? 'No registrado' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Correo</p>
                    <p class="text-white">{{ $venta->comprador->correo ?? 'No registrado' }}</p>
                </div>
                @if($venta->cliente)
                    <div>
                        <p class="text-gray-400 text-sm">Cliente de feria relacionado</p>
                        <p class="text-white">{{ $venta->cliente->nombre }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-xl font-semibold mb-6">Vehículo</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-gray-400 text-sm">Placa</p>
                    <p class="text-white font-medium text-lg">{{ $venta->vehiculo->placa ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Marca / Modelo</p>
                    <p class="text-white">{{ $venta->vehiculo->marca ?? '' }} {{ $venta->vehiculo->modelo ?? '' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Concesionario que lo tenía</p>
                    <p class="text-white">{{ $venta->vehiculo->concesionario->nombre ?? 'Sin asignar' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 md:col-span-2">
            <h2 class="text-xl font-semibold mb-6">Venta</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-gray-400 text-sm">Concesionario que vende</p>
                    <p class="text-white font-medium">{{ $venta->concesionarioVende->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Asesor comercial</p>
                    <p class="text-white font-medium">{{ $venta->asesorComercial->nombre ?? '—' }}</p>
                    <p class="text-gray-500 text-xs">CC {{ $venta->asesorComercial->cedula ?? '—' }} · {{ $venta->asesorComercial->telefono ?? '' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Forma de pago</p>
                    <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400">
                        {{ $formaPagoLabel }}
                    </span>
                </div>
                @if($venta->banco)
                    <div>
                        <p class="text-gray-400 text-sm">Banco</p>
                        <p class="text-white font-medium">{{ $venta->banco }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-gray-400 text-sm">Valor</p>
                    <p class="text-white font-medium text-lg">$ {{ number_format($venta->valor, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Fecha</p>
                    <p class="text-white">{{ $venta->fecha_venta?->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Registrada por</p>
                    <p class="text-white">{{ $venta->usuario->name ?? '—' }}</p>
                </div>
                @if($venta->tiene_retoma)
                    <div>
                        <p class="text-gray-400 text-sm">Retoma</p>
                        <p class="text-white font-medium">$ {{ number_format($venta->retoma_valor, 0, ',', '.') }}</p>
                        <p class="text-gray-500 text-xs">{{ $venta->retoma_descripcion }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($venta->observaciones)
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 md:col-span-2">
                <h2 class="text-xl font-semibold mb-4">Observaciones</h2>
                <p class="text-gray-300 whitespace-pre-wrap">{{ $venta->observaciones }}</p>
            </div>
        @endif

    </div>

</div>

@endsection
