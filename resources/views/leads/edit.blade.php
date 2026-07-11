@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold">Editar Lead</h1>
            <p class="text-gray-400 mt-1 text-sm">{{ $lead->full_name ?: 'Sin nombre' }}</p>
        </div>
        <a href="{{ route('leads.show', $lead) }}"
            class="bg-gray-800 hover:bg-gray-700 px-4 py-2.5 rounded-xl transition text-sm">
            Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-500/10 border border-red-500/50 rounded-xl p-4">
            <p class="text-red-400 font-semibold mb-2">Errores:</p>
            <ul class="text-red-400 text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-lg font-semibold mb-4 text-gray-300">Datos de Meta (solo lectura)</h2>
            <div class="space-y-3 text-sm">
                <div><p class="text-gray-500">Nombre</p><p class="text-white">{{ $lead->full_name ?: '—' }}</p></div>
                <div><p class="text-gray-500">Email</p><p class="text-white">{{ $lead->email ?: '—' }}</p></div>
                <div><p class="text-gray-500">Teléfono</p><p>@if($lead->phone_number)<a href="{{ $lead->whatsapp_url }}" target="_blank" rel="noopener" class="text-green-400 hover:text-green-300 hover:underline">{{ $lead->phone_number }}</a>@else<span class="text-white">—</span>@endif</p></div>
                <div><p class="text-gray-500">Campaña</p><p class="text-white">{{ $lead->campaign_name ?: '—' }}</p></div>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-lg font-semibold mb-4">Gestión interna</h2>
            <form method="POST" action="{{ route('leads.update', $lead) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block mb-2 text-sm text-gray-400">Estado de gestión</label>
                    <select name="estado_gestion" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
                        @foreach(['Nuevo', 'Contactado', 'Negociacion', 'Vendido', 'Perdido'] as $estado)
                            <option value="{{ $estado }}" @selected(old('estado_gestion', $lead->estado_gestion) === $estado)>{{ $estado }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block mb-2 text-sm text-gray-400">Observaciones</label>
                    <textarea name="observaciones" rows="5"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">{{ old('observaciones', $lead->observaciones) }}</textarea>
                </div>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl font-medium transition">
                    Guardar Cambios
                </button>
            </form>
        </div>

    </div>

</div>

@endsection
