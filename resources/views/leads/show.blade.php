@extends('layouts.app')

@section('content')

@php
    $estadoConfig = [
        'Nuevo'       => ['badge' => 'bg-blue-500/20 text-blue-400',   'label' => 'Nuevo'],
        'Contactado'  => ['badge' => 'bg-teal-500/20 text-teal-400',   'label' => 'Contactado'],
        'Negociacion' => ['badge' => 'bg-amber-500/20 text-amber-400', 'label' => 'Negociación'],
        'Vendido'     => ['badge' => 'bg-green-500/20 text-green-400', 'label' => 'Vendido'],
        'Perdido'     => ['badge' => 'bg-red-500/20 text-red-400',     'label' => 'Perdido'],
    ];
    $cfg = $estadoConfig[$lead->estado_gestion] ?? ['badge' => 'bg-gray-700 text-gray-300', 'label' => $lead->estado_gestion];
@endphp

<div class="max-w-4xl mx-auto">

    <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold">{{ $lead->full_name ?: 'Sin nombre' }}</h1>
            <p class="text-gray-400 mt-1 text-sm">Detalle del lead</p>
        </div>
        <div class="flex gap-2 shrink-0">
            @can('update', $lead)
                <a href="{{ route('leads.edit', $lead) }}"
                    class="bg-blue-600 hover:bg-blue-700 px-4 py-2.5 rounded-xl transition font-medium text-sm">
                    Editar
                </a>
            @endcan
            @can('delete', $lead)
                <form method="POST" action="{{ route('leads.destroy', $lead) }}" onsubmit="return confirm('¿Eliminar este lead?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-red-500/20 hover:bg-red-500/40 text-red-400 px-4 py-2.5 rounded-xl transition font-medium text-sm">
                        Eliminar
                    </button>
                </form>
            @endcan
            <a href="{{ route('leads.index') }}"
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-xl font-semibold mb-6">Contacto</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-gray-400 text-sm">Nombre</p>
                    <p class="text-white font-medium text-lg">{{ $lead->full_name ?: 'No registrado' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Email</p>
                    <p class="text-white">{{ $lead->email ?? 'No registrado' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Teléfono</p>
                    <p class="text-white">{{ $lead->phone_number ?? 'No registrado' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Actividad económica</p>
                    <p class="text-white">{{ $lead->actividad_economica ?? 'No registrado' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Monto de interés a aprobar</p>
                    <p class="text-white">{{ $lead->monto_interes_aprobar ?? 'No registrado' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-xl font-semibold mb-6">Gestión interna</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-gray-400 text-sm">Concesionario asignado</p>
                    <p class="text-white font-medium">{{ $lead->concesionario->nombre ?? 'Sin asignar' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Estado</p>
                    <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-medium {{ $cfg['badge'] }}">
                        {{ $cfg['label'] }}
                    </span>
                    @if($lead->vencido)
                        <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-400 ml-1">
                            Vencido
                        </span>
                    @endif
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Asignado desde</p>
                    <p class="text-white">{{ $lead->assigned_at?->format('d/m/Y H:i') ?? 'Sin asignar' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Estado en Meta (informativo)</p>
                    <p class="text-white">{{ $lead->meta_lead_status ?? '—' }}</p>
                </div>
                @if($lead->observaciones)
                    <div>
                        <p class="text-gray-400 text-sm">Observaciones</p>
                        <p class="text-white whitespace-pre-wrap">{{ $lead->observaciones }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 md:col-span-2">
            <h2 class="text-xl font-semibold mb-6">Origen (Meta Ads)</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div><p class="text-gray-400">Campaña</p><p class="text-white">{{ $lead->campaign_name ?? '—' }}</p></div>
                <div><p class="text-gray-400">Conjunto de anuncios</p><p class="text-white">{{ $lead->adset_name ?? '—' }}</p></div>
                <div><p class="text-gray-400">Anuncio</p><p class="text-white">{{ $lead->ad_name ?? '—' }}</p></div>
                <div><p class="text-gray-400">Formulario</p><p class="text-white">{{ $lead->form_name ?? '—' }}</p></div>
                <div><p class="text-gray-400">Plataforma</p><p class="text-white">{{ $lead->platform ?? '—' }}</p></div>
                <div><p class="text-gray-400">Orgánico</p><p class="text-white">{{ $lead->is_organic ? 'Sí' : 'No' }}</p></div>
                <div><p class="text-gray-400">Fecha del lead (Meta)</p><p class="text-white">{{ $lead->created_time?->format('d/m/Y H:i') ?? '—' }}</p></div>
            </div>
        </div>

        @can('reassign', $lead)
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 md:col-span-2">
                <h2 class="text-xl font-semibold mb-4">Reasignar</h2>
                <form method="POST" action="{{ route('leads.reassign', $lead) }}" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    @method('PATCH')
                    <div class="flex-1 min-w-[200px]">
                        <label class="text-xs text-gray-400">Nuevo concesionario</label>
                        <select name="to_concesionario_id" required
                            class="w-full mt-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                            @foreach($concesionarios as $c)
                                <option value="{{ $c->id }}" @selected($c->id === $lead->concesionario_id)>{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="text-xs text-gray-400">Motivo (opcional)</label>
                        <input type="text" name="motivo"
                            class="w-full mt-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                    </div>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl text-sm font-medium transition">
                        Reasignar
                    </button>
                </form>
            </div>
        @endcan

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 md:col-span-2">
            <h2 class="text-xl font-semibold mb-4">Historial de reasignaciones</h2>
            @forelse($lead->reassignments as $r)
                <div class="text-sm text-gray-400 border-b border-gray-800 py-3 last:border-b-0">
                    <span class="text-white">{{ $r->fromConcesionario->nombre ?? 'Sin asignar' }}</span>
                    →
                    <span class="text-white">{{ $r->toConcesionario->nombre ?? '—' }}</span>
                    por <span class="text-white">{{ $r->reassignedBy->name ?? '—' }}</span>
                    el {{ $r->created_at->format('d/m/Y H:i') }}
                    @if($r->motivo)
                        <div class="text-xs text-gray-500 mt-1">Motivo: {{ $r->motivo }}</div>
                    @endif
                </div>
            @empty
                <p class="text-gray-500 text-sm">Este lead no ha sido reasignado.</p>
            @endforelse
        </div>

    </div>

</div>

@endsection
