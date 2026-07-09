@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

<div class="flex flex-wrap items-start justify-between gap-4 mb-8">

    <div>
        <h1 class="text-2xl lg:text-4xl font-bold">
            {{ $vehiculo->marca }} {{ $vehiculo->linea }}
        </h1>
        <p class="text-gray-400 mt-1 text-sm lg:text-base">
            {{ $vehiculo->version }} • Modelo {{ $vehiculo->modelo }}
        </p>
    </div>

    <div class="flex gap-2 shrink-0">
        @can('update', $vehiculo)
            <a href="{{ route('vehiculos.edit', $vehiculo) }}"
                class="bg-yellow-500 hover:bg-yellow-600 px-4 py-2.5 rounded-xl text-sm font-medium transition">
                Editar
            </a>
        @endcan
        <a href="{{ route('vehiculos.index') }}"
            class="bg-gray-700 hover:bg-gray-600 px-4 py-2.5 rounded-xl text-sm transition">
            Volver
        </a>
    </div>

</div>

<!-- RESUMEN -->

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">

        <p class="text-gray-400 text-sm">
            Placa
        </p>

        <h3 class="text-2xl font-bold mt-2">
            {{ $vehiculo->placa }}
        </h3>

    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">

        <p class="text-gray-400 text-sm">
            Kilometraje
        </p>

        <h3 class="text-2xl font-bold mt-2">
            {{ number_format($vehiculo->kilometraje,0,',','.') }} km
        </h3>

    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">

        <p class="text-gray-400 text-sm">
            Precio Expocar
        </p>

        <h3 class="text-2xl font-bold text-green-400 mt-2">

            $ {{ number_format($vehiculo->precio_expocar,0,',','.') }}

        </h3>

    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">

        <p class="text-gray-400 text-sm">
            Estado
        </p>

        <h3 class="text-xl font-bold mt-2">

            @if($vehiculo->estado == 'Disponible')

                <span class="text-green-400">
                    Disponible
                </span>

            @elseif($vehiculo->estado == 'Reservado')

                <span class="text-yellow-400">
                    Reservado
                </span>

            @else

                <span class="text-red-400">
                    Vendido
                </span>

            @endif

        </h3>

    </div>

</div>

<!-- INFORMACIÓN -->

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">

        <h2 class="text-xl font-bold mb-6 text-blue-400">

            Información General

        </h2>

        <div class="space-y-4">

            <p>
                <strong>Concesionario:</strong>
                {{ $vehiculo->concesionario?->nombre }}
            </p>

            <p>
                <strong>Marca:</strong>
                {{ $vehiculo->marca }}
            </p>

            <p>
                <strong>Línea:</strong>
                {{ $vehiculo->linea }}
            </p>

            <p>
                <strong>Versión:</strong>
                {{ $vehiculo->version }}
            </p>

            <p>
                <strong>Modelo:</strong>
                {{ $vehiculo->modelo }}
            </p>

            <p>
                <strong>Color:</strong>
                {{ $vehiculo->color }}
            </p>

        </div>

    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">

        <h2 class="text-xl font-bold mb-6 text-green-400">

            Características

        </h2>

        <div class="space-y-4">

            <p>
                <strong>Clase:</strong>
                {{ $vehiculo->clase_vehiculo }}
            </p>

            <p>
                <strong>Tipo:</strong>
                {{ $vehiculo->tipo_vehiculo }}
            </p>

            <p>
                <strong>CC:</strong>
                {{ $vehiculo->cc }}
            </p>

            <p>
                <strong>Combustible:</strong>
                {{ $vehiculo->combustible }}
            </p>

            <p>
                <strong>Transmisión:</strong>
                {{ $vehiculo->transmision }}
            </p>

        </div>

    </div>

</div>

<!-- DOCUMENTOS -->

<div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mt-6">

    <h2 class="text-xl font-bold mb-6 text-yellow-400">

        Documentación

    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div>

            <p class="text-gray-400">
                Fecha Matrícula
            </p>

            <p>
                {{ $vehiculo->fecha_matricula }}
            </p>

        </div>

        <div>

            <p class="text-gray-400">
                SOAT
            </p>

            <p>
                {{ $vehiculo->fecha_soat }}
            </p>

        </div>

        <div>

            <p class="text-gray-400">
                Tecnomecánica
            </p>

            <p>
                {{ $vehiculo->fecha_tecno }}
            </p>

        </div>

    </div>

</div>

<!-- VALORES -->

<div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mt-6">

    <h2 class="text-xl font-bold mb-6 text-purple-400">

        Valores Comerciales

    </h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        <div>

            <p class="text-gray-400">
                Fasecolda
            </p>

            <p>
                $ {{ number_format($vehiculo->pr_fasecolda,0,',','.') }}
            </p>

        </div>

        <div>

            <p class="text-gray-400">
                Precio Normal
            </p>

            <p>
                $ {{ number_format($vehiculo->precio_normal,0,',','.') }}
            </p>

        </div>

        <div>

            <p class="text-gray-400">
                Bono
            </p>

            <p>
                $ {{ number_format($vehiculo->bono_descuento,0,',','.') }}
            </p>

        </div>

        <div>

            <p class="text-gray-400">
                Precio Expocar
            </p>

            <p class="text-green-400 font-bold">
                $ {{ number_format($vehiculo->precio_expocar,0,',','.') }}
            </p>

        </div>

    </div>

</div>

</div>

@endsection
