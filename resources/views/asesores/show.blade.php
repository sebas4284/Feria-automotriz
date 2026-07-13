@extends('layouts.app')

@section('content')

    <div class="max-w-4xl mx-auto">

        <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold">{{ $asesor->nombre }}</h1>
                <p class="text-gray-400 mt-1 text-sm">Detalle del asesor comercial</p>
            </div>
            <div class="flex gap-2 shrink-0">
                @can('update', $asesor)
                    <a href="{{ route('asesores.edit', $asesor) }}"
                        class="bg-blue-600 hover:bg-blue-700 px-4 py-2.5 rounded-xl transition font-medium text-sm">
                        Editar
                    </a>
                @endcan
                <a href="{{ route('asesores.index') }}"
                    class="bg-gray-800 hover:bg-gray-700 px-4 py-2.5 rounded-xl transition text-sm">
                    Volver
                </a>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <div class="space-y-4">
                <div>
                    <p class="text-gray-400 text-sm">Cédula</p>
                    <p class="text-white">{{ $asesor->cedula }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Teléfono</p>
                    <p class="text-white">{{ $asesor->telefono ?? 'No registrado' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Concesionario</p>
                    <p class="text-white">{{ $asesor->concesionario->nombre ?? 'Sin asignar' }}</p>
                </div>
            </div>
        </div>

    </div>

@include('partials._auto-refresh')
@endsection
