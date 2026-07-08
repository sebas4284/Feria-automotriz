@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="flex items-center justify-between mb-6 gap-3">
        <div>
            <h1 class="text-2xl font-bold">{{ $lead->nombre }}</h1>
            <p class="text-gray-400 text-sm mt-0.5">Detalle del lead</p>
        </div>
        <div class="flex gap-2 shrink-0">
            <a href="{{ route('leads.edit', $lead) }}"
                class="bg-blue-600 hover:bg-blue-700 px-4 py-2.5 rounded-xl text-sm font-medium transition">
                Gestionar
            </a>
            <a href="{{ route('leads.index') }}"
                class="bg-gray-800 hover:bg-gray-700 px-4 py-2.5 rounded-xl text-sm transition">
                Volver
            </a>
        </div>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <div>
                <p class="text-gray-400 text-sm">Nombre</p>
                <p class="font-semibold mt-0.5">{{ $lead->nombre }}</p>
            </div>

            <div>
                <p class="text-gray-400 text-sm">Teléfono</p>
                <p class="font-semibold mt-0.5">{{ $lead->telefono }}</p>
            </div>

            <div>
                <p class="text-gray-400 text-sm">Vehículo de interés</p>
                <p class="font-semibold mt-0.5">{{ $lead->vehiculo_interes ?: '—' }}</p>
            </div>

            <div>
                <p class="text-gray-400 text-sm">Concesionario</p>
                <p class="font-semibold mt-0.5">{{ $lead->concesionario?->nombre ?: '—' }}</p>
            </div>

            <div>
                <p class="text-gray-400 text-sm">Estado</p>
                <p class="font-semibold mt-0.5">{{ $lead->estado }}</p>
            </div>

            <div>
                <p class="text-gray-400 text-sm">Reasignaciones</p>
                <p class="font-semibold mt-0.5">{{ $lead->reasignaciones }}</p>
            </div>

            <div>
                <p class="text-gray-400 text-sm">Última gestión</p>
                <p class="font-semibold mt-0.5">
                    @if($lead->ultima_gestion)
                        {{ \Carbon\Carbon::parse($lead->ultima_gestion)->format('d/m/Y H:i') }}
                    @else
                        <span class="text-red-400">Sin gestionar</span>
                    @endif
                </p>
            </div>

            <div>
                <p class="text-gray-400 text-sm">Registrado</p>
                <p class="font-semibold mt-0.5">{{ $lead->created_at->format('d/m/Y') }}</p>
            </div>

        </div>

        @if($lead->observacion)
            <div class="mt-6 pt-6 border-t border-gray-800">
                <p class="text-gray-400 text-sm mb-2">Observación</p>
                <div class="bg-gray-800 rounded-xl p-4 text-gray-300 text-sm">
                    {{ $lead->observacion }}
                </div>
            </div>
        @endif

    </div>

</div>

@endsection
