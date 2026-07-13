@extends('layouts.app')

@section('content')

    <div class="max-w-4xl mx-auto">

        <div class="flex flex-wrap items-start justify-between gap-4 mb-8">

            <div>
                <h1 class="text-2xl lg:text-3xl font-bold">
                    {{ $cliente->nombre }}
                </h1>
                <p class="text-gray-400 mt-1 text-sm">Detalles del cliente</p>
            </div>

            <div class="flex gap-2 shrink-0">
                <a href="{{ route('clientes.edit', $cliente) }}"
                    class="bg-blue-600 hover:bg-blue-700 px-4 py-2.5 rounded-xl transition font-medium text-sm">
                    Editar
                </a>
                <a href="{{ route('clientes.index') }}"
                    class="bg-gray-800 hover:bg-gray-700 px-4 py-2.5 rounded-xl transition text-sm">
                    Volver
                </a>
            </div>

        </div>

        <!-- Grid de información -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Información básica -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h2 class="text-xl font-semibold mb-6">Información Básica</h2>

                <div class="space-y-4">
                    <div>
                        <p class="text-gray-400 text-sm">Nombre</p>
                        <p class="text-white font-medium text-lg">{{ $cliente->nombre }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-sm">Teléfono</p>
                        <p class="text-white">{{ $cliente->telefono ?? 'No registrado' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-sm">¿Cómo se enteró?</p>
                        <p class="text-white">{{ $cliente->medio_entero ?? 'No especificado' }}</p>
                    </div>
                </div>
            </div>

            <!-- Información de gestión -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h2 class="text-xl font-semibold mb-6">Gestión</h2>

                <div class="space-y-4">
                    <div>
                        <p class="text-gray-400 text-sm">Concesionario asignado</p>
                        <p class="text-white font-medium">{{ $cliente->concesionario->nombre ?? 'Sin asignar' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-sm">Cita agendada</p>
                        <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-medium {{ $cliente->cita ? 'bg-green-500/20 text-green-400' : 'bg-gray-700 text-gray-300' }}">
                            {{ $cliente->cita ? 'Sí' : 'No' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Observaciones -->
        @if ($cliente->observaciones)
            <div class="mt-6 bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h2 class="text-xl font-semibold mb-4">Observaciones</h2>
                <p class="text-gray-300 whitespace-pre-wrap">{{ $cliente->observaciones }}</p>
            </div>
        @endif

        <!-- Historial -->
        <div class="mt-6 bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-xl font-semibold mb-4">Historial</h2>

            <div class="space-y-3 text-sm text-gray-400">
                <div>
                    <p>Cliente creado el <span class="text-white">{{ $cliente->created_at->format('d/m/Y H:i') }}</span></p>
                </div>
                <div>
                    <p>Última actualización el <span class="text-white">{{ $cliente->updated_at->format('d/m/Y H:i') }}</span></p>
                </div>
            </div>
        </div>

        <!-- Botón de eliminación -->
        <div class="mt-8">
            <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('¿Estás seguro de que deseas eliminar este cliente?')"
                    class="bg-red-500 hover:bg-red-600 px-6 py-3 rounded-xl font-medium transition">
                    🗑️ Eliminar Cliente
                </button>
            </form>
        </div>

    </div>

@endsection
