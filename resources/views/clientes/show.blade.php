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
                        <p class="text-gray-400 text-sm">Email</p>
                        <p class="text-white">{{ $cliente->email ?? 'No registrado' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-sm">Teléfono</p>
                        <p class="text-white">{{ $cliente->telefono ?? 'No registrado' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-sm">Ciudad</p>
                        <p class="text-white">{{ $cliente->ciudad ?? 'No registrado' }}</p>
                    </div>
                </div>
            </div>

            <!-- Información de compra -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h2 class="text-xl font-semibold mb-6">Información de Compra</h2>

                <div class="space-y-4">
                    <div>
                        <p class="text-gray-400 text-sm">Vehículo de Interés</p>
                        <p class="text-white font-medium">{{ $cliente->vehiculo_interes ?? 'No especificado' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-sm">Presupuesto</p>
                        <p class="text-white font-medium text-lg">
                            @if ($cliente->presupuesto)
                                $ {{ number_format($cliente->presupuesto, 0, ',', '.') }}
                            @else
                                No especificado
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-sm">Estado</p>
                        <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-medium
                            @if ($cliente->estado === 'Nuevo')
                                bg-blue-500/20 text-blue-400
                            @elseif ($cliente->estado === 'Contactado')
                                bg-green-500/20 text-green-400
                            @elseif ($cliente->estado === 'Negociacion')
                                bg-yellow-500/20 text-yellow-400
                            @elseif ($cliente->estado === 'Vendido')
                                bg-emerald-500/20 text-emerald-400
                            @elseif ($cliente->estado === 'Perdido')
                                bg-red-500/20 text-red-400
                            @endif
                        ">
                            {{ $cliente->estado }}
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
