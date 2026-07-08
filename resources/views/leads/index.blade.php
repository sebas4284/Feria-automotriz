@extends('layouts.app')

@section('content')

@php
    $estadoLeadConfig = [
        'Nuevo'      => ['badge' => 'bg-blue-500/20 text-blue-400',    'dot' => 'bg-blue-400'],
        'Contactado' => ['badge' => 'bg-teal-500/20 text-teal-400',    'dot' => 'bg-teal-400'],
        'Interesado' => ['badge' => 'bg-purple-500/20 text-purple-400','dot' => 'bg-purple-400'],
        'Cita'       => ['badge' => 'bg-yellow-500/20 text-yellow-400','dot' => 'bg-yellow-400'],
        'Negociacion'=> ['badge' => 'bg-orange-500/20 text-orange-400','dot' => 'bg-orange-400'],
        'Vendido'    => ['badge' => 'bg-emerald-500/20 text-emerald-400','dot' => 'bg-emerald-400'],
        'Perdido'    => ['badge' => 'bg-red-500/20 text-red-400',      'dot' => 'bg-red-400'],
        'Reasignado' => ['badge' => 'bg-gray-500/20 text-gray-400',    'dot' => 'bg-gray-400'],
    ];

    $avatarColors = ['bg-blue-600','bg-purple-600','bg-green-600','bg-amber-600','bg-red-600','bg-teal-600','bg-pink-600','bg-indigo-600'];

    function leadColor($nombre, $colors) {
        return $colors[abs(crc32($nombre)) % count($colors)];
    }

    function leadInitials($nombre) {
        $words = array_filter(explode(' ', $nombre));
        $initials = '';
        foreach (array_slice(array_values($words), 0, 2) as $w) {
            $initials .= strtoupper($w[0]);
        }
        return $initials;
    }
@endphp

{{-- ===================== VISTA MÓVIL ===================== --}}
<div class="lg:hidden space-y-5">

    {{-- Encabezado --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold">Leads</h1>
            <p class="text-gray-400 text-sm mt-0.5">Gestión de oportunidades</p>
        </div>
        <a href="{{ route('leads.create') }}"
            class="w-11 h-11 bg-blue-600 hover:bg-blue-700 rounded-2xl flex items-center justify-center transition shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </a>
    </div>

    {{-- Contadores por estado (scroll horizontal) --}}
    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach($leads->groupBy('estado') as $estado => $grupo)
            @php $cfg = $estadoLeadConfig[$estado] ?? ['badge' => 'bg-gray-500/20 text-gray-400', 'dot' => 'bg-gray-400']; @endphp
            <div class="flex-shrink-0 bg-gray-900 border border-gray-800 rounded-2xl px-3 py-2.5 text-center min-w-[80px]">
                <p class="text-lg font-bold {{ explode(' ', $cfg['badge'])[1] }}">{{ $grupo->count() }}</p>
                <p class="text-xs text-gray-500 mt-0.5 whitespace-nowrap">{{ $estado }}</p>
            </div>
        @endforeach
    </div>

    {{-- Lista de leads --}}
    <div class="space-y-2">
        @forelse($leads as $lead)
            @php
                $cfg      = $estadoLeadConfig[$lead->estado] ?? ['badge' => 'bg-gray-500/20 text-gray-400', 'dot' => 'bg-gray-400'];
                $color    = leadColor($lead->nombre, $avatarColors);
                $initials = leadInitials($lead->nombre);

                if (!$lead->ultima_gestion) {
                    $gestionClass = 'text-red-400';
                    $gestionLabel = 'Sin gestionar';
                } elseif (\Carbon\Carbon::parse($lead->ultima_gestion)->diffInDays(now()) >= 3) {
                    $gestionClass = 'text-yellow-400';
                    $gestionLabel = \Carbon\Carbon::parse($lead->ultima_gestion)->diffForHumans();
                } else {
                    $gestionClass = 'text-gray-500';
                    $gestionLabel = \Carbon\Carbon::parse($lead->ultima_gestion)->diffForHumans();
                }
            @endphp
            <div class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3.5 hover:border-gray-700 transition">
                <div class="flex items-center gap-3">
                    <div class="{{ $color }} w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-medium text-sm truncate">{{ $lead->nombre }}</p>
                            <span class="text-xs {{ $cfg['badge'] }} px-2 py-0.5 rounded-full shrink-0">{{ $lead->estado }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 mt-0.5">
                            <p class="text-xs text-gray-500 truncate">{{ $lead->vehiculo_interes ?: $lead->telefono }}</p>
                            <p class="text-xs {{ $gestionClass }} shrink-0">{{ $gestionLabel }}</p>
                        </div>
                        @if($lead->concesionario)
                            <p class="text-xs text-gray-600 mt-0.5 truncate">{{ $lead->concesionario->nombre }}</p>
                        @endif
                    </div>
                </div>
                {{-- Acciones compactas --}}
                <div class="flex gap-2 mt-3 pt-3 border-t border-gray-800">
                    <a href="{{ route('leads.show', $lead) }}"
                        class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Ver
                    </a>
                    <a href="{{ route('leads.edit', $lead) }}"
                        class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-xl bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 text-xs transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                        </svg>
                        Gestionar
                    </a>
                    @if($lead->reasignaciones > 0)
                        <span class="flex items-center px-3 py-1.5 rounded-xl bg-yellow-500/10 text-yellow-400 text-xs font-semibold">
                            {{ $lead->reasignaciones }}✕
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-10 h-10 mx-auto mb-3 text-gray-700">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                </svg>
                No hay leads registrados
            </div>
        @endforelse
    </div>

</div>

{{-- ===================== VISTA DESKTOP ===================== --}}
<div class="hidden lg:block">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Leads</h1>
            <p class="text-gray-400">Gestión de oportunidades</p>
        </div>
        @include('partials._boton-crear', ['href' => route('leads.create'), 'texto' => 'Nuevo Lead'])
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-800">
                <tr>
                    <th class="p-4 text-left">Nombre</th>
                    <th class="p-4 text-left hidden sm:table-cell">Vehículo</th>
                    <th class="p-4 text-left hidden sm:table-cell">Concesionario</th>
                    <th class="p-4 text-left">Estado</th>
                    <th class="p-4 text-left hidden md:table-cell">Última Gestión</th>
                    <th class="p-4 text-left hidden md:table-cell">Reasignaciones</th>
                    <th class="p-4 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                    @php $cfg = $estadoLeadConfig[$lead->estado] ?? ['badge' => 'bg-gray-500/20 text-gray-400', 'dot' => 'bg-gray-400']; @endphp
                    <tr class="border-t border-gray-800 hover:bg-gray-800/40">
                        <td class="p-4">
                            <div class="font-medium">{{ $lead->nombre }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $lead->telefono }}</div>
                            <div class="sm:hidden text-xs text-gray-500 mt-0.5">{{ $lead->concesionario?->nombre ?? 'Sin asignar' }}</div>
                        </td>
                        <td class="p-4 hidden sm:table-cell">{{ $lead->vehiculo_interes }}</td>
                        <td class="p-4 hidden sm:table-cell">{{ $lead->concesionario?->nombre ?? 'Sin asignar' }}</td>
                        <td class="p-4">
                            <span class="text-xs {{ $cfg['badge'] }} px-3 py-1 rounded-full">{{ $lead->estado }}</span>
                        </td>
                        <td class="p-4 hidden md:table-cell">
                            @if(!$lead->ultima_gestion)
                                <span class="text-red-400 font-semibold text-sm">Nunca gestionado</span>
                            @elseif(\Carbon\Carbon::parse($lead->ultima_gestion)->diffInDays(now()) >= 3)
                                <span class="text-yellow-400 font-semibold text-sm">{{ \Carbon\Carbon::parse($lead->ultima_gestion)->diffForHumans() }}</span>
                            @else
                                <span class="text-green-400 text-sm">{{ \Carbon\Carbon::parse($lead->ultima_gestion)->diffForHumans() }}</span>
                            @endif
                        </td>
                        <td class="p-4 hidden md:table-cell">
                            @if($lead->reasignaciones > 0)
                                <span class="bg-yellow-500 text-black px-3 py-1 rounded-full text-sm font-semibold">{{ $lead->reasignaciones }}</span>
                            @else
                                <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="p-4">
                            @include('partials._acciones', [
                                'modelo'      => $lead,
                                'ruta'        => 'leads',
                                'label'       => 'lead',
                                'labelEditar' => 'Gestionar',
                                'sinEliminar' => true,
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center p-8 text-gray-400">No hay leads registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection
