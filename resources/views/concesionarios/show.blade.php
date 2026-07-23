@extends('layouts.app')

@section('content')

    <div class="max-w-4xl mx-auto">

        <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold">{{ $concesionario->nombre }}</h1>
                <p class="text-gray-400 mt-1 text-sm">Detalle del concesionario</p>
            </div>
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('concesionarios.edit', $concesionario) }}"
                    class="bg-blue-600 hover:bg-blue-700 px-4 py-2.5 rounded-xl transition font-medium text-sm">
                    Editar
                </a>
                <a href="{{ route('concesionarios.index') }}"
                    class="bg-gray-800 hover:bg-gray-700 px-4 py-2.5 rounded-xl transition text-sm">
                    Volver
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h2 class="text-xl font-semibold mb-6">Información</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-gray-400 text-sm">NIT</p>
                        <p class="text-white">{{ $concesionario->nit ?? 'No registrado' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Ciudad</p>
                        <p class="text-white">{{ $concesionario->ciudad ?? 'No registrado' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Teléfono</p>
                        <p class="text-white">{{ $concesionario->telefono ?? 'No registrado' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Email</p>
                        <p class="text-white">{{ $concesionario->email ?? 'No registrado' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h2 class="text-xl font-semibold mb-6">Asignación</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-gray-400 text-sm">Responsable</p>
                        <p class="text-white">{{ $concesionario->responsable ?? 'No registrado' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Peso de asignación</p>
                        <p class="text-white font-medium text-lg">{{ $concesionario->peso_asignacion }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Cupo feria</p>
                        <p class="text-white font-medium text-lg">{{ $concesionario->cupo_feria ?? 'Sin límite' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Estado</p>
                        <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-medium {{ $concesionario->activo ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' }}">
                            {{ $concesionario->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection
