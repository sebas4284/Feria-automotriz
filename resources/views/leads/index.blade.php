@extends('layouts.app')

@section('content')

@php
    $estadoConfig = [
        'Nuevo'       => ['badge' => 'bg-blue-500/20 text-blue-400',    'label' => 'Nuevo'],
        'Asignado'    => ['badge' => 'bg-purple-500/20 text-purple-400', 'label' => 'Asignado'],
        'Contactado'  => ['badge' => 'bg-teal-500/20 text-teal-400',    'label' => 'Contactado'],
        'Negociacion' => ['badge' => 'bg-amber-500/20 text-amber-400',  'label' => 'Negociación'],
        'Vendido'     => ['badge' => 'bg-green-500/20 text-green-400',  'label' => 'Vendido'],
        'Perdido'     => ['badge' => 'bg-red-500/20 text-red-400',      'label' => 'Perdido'],
    ];
@endphp

<div>

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold">Leads</h1>
            <p class="text-gray-400 mt-1 text-sm">Leads capturados desde Meta Ads (sincronizados desde el sheet)</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/50 rounded-xl p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 lg:p-5">
            <p class="text-gray-400 text-xs lg:text-sm font-medium mb-1">Total leads</p>
            <p class="text-2xl lg:text-4xl font-bold">{{ $leads->total() }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 lg:p-5">
            <p class="text-gray-400 text-xs lg:text-sm font-medium mb-1">Nuevos hoy</p>
            <p class="text-2xl lg:text-4xl font-bold text-blue-400">{{ $totalNuevos }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 lg:p-5">
            <p class="text-gray-400 text-xs lg:text-sm font-medium mb-1">Vencidos ({{ config('leads.staleness_hours') }}h)</p>
            <p class="text-2xl lg:text-4xl font-bold text-red-400">{{ $totalVencidos }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 lg:p-5">
            <p class="text-gray-400 text-xs lg:text-sm font-medium mb-1">Sin asesor</p>
            <p class="text-2xl lg:text-4xl font-bold text-teal-400">{{ $totalSinAsesor }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 lg:p-5">
            <p class="text-gray-400 text-xs lg:text-sm font-medium mb-1">Contactados</p>
            <p class="text-2xl lg:text-4xl font-bold text-cyan-400">{{ $totalContactados }}</p>
        </div>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">

        <div class="p-5 border-b border-gray-800 flex flex-wrap gap-3 justify-between items-center">
            <div class="flex flex-wrap gap-2 items-center">
                <a href="{{ route('leads.index', array_filter(['buscar' => request('buscar'), 'concesionario_id' => request('concesionario_id')])) }}"
                    class="px-3 py-1.5 rounded-xl text-sm transition {{ ! request('filtro') ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                    Todos
                </a>
                <a href="{{ route('leads.index', array_filter(['buscar' => request('buscar'), 'filtro' => 'vencido', 'concesionario_id' => request('concesionario_id')])) }}"
                    class="px-3 py-1.5 rounded-xl text-sm transition {{ request('filtro') === 'vencido' ? 'bg-red-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                    Vencidos
                </a>
                <a href="{{ route('leads.index', array_filter(['buscar' => request('buscar'), 'filtro' => 'sin_asesor', 'concesionario_id' => request('concesionario_id')])) }}"
                    class="px-3 py-1.5 rounded-xl text-sm transition {{ request('filtro') === 'sin_asesor' ? 'bg-teal-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                    Sin asesor
                </a>
                <a href="{{ route('leads.index', array_filter(['buscar' => request('buscar'), 'filtro' => 'contactado', 'concesionario_id' => request('concesionario_id')])) }}"
                    class="px-3 py-1.5 rounded-xl text-sm transition {{ request('filtro') === 'contactado' ? 'bg-cyan-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                    Contactados
                </a>

                @if(auth()->user()->isAdmin())
                    <select onchange="window.location.href = this.value"
                        class="bg-gray-800 border border-gray-700 rounded-xl px-3 py-1.5 text-sm text-gray-300 focus:outline-none focus:border-blue-500">
                        <option value="{{ route('leads.index', array_filter(['buscar' => request('buscar'), 'filtro' => request('filtro')])) }}">
                            Todos los concesionarios
                        </option>
                        @foreach($concesionarios as $c)
                            <option value="{{ route('leads.index', array_filter(['buscar' => request('buscar'), 'filtro' => request('filtro'), 'concesionario_id' => $c->id])) }}"
                                @selected((string) request('concesionario_id') === (string) $c->id)>
                                {{ $c->nombre }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div class="flex gap-2">
                <form method="GET" action="{{ route('leads.index') }}" class="flex gap-2">
                    @if(request('filtro'))
                        <input type="hidden" name="filtro" value="{{ request('filtro') }}">
                    @endif
                    @if(request('concesionario_id'))
                        <input type="hidden" name="concesionario_id" value="{{ request('concesionario_id') }}">
                    @endif
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        oninput="clearTimeout(window.__buscarLeadsTimeout); window.__buscarLeadsTimeout = setTimeout(() => this.form.submit(), 500)"
                        placeholder="Buscar por nombre, email o teléfono..."
                        class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm w-64 focus:outline-none focus:border-blue-500">
                    @if(request('buscar'))
                        <a href="{{ route('leads.index', array_filter(['filtro' => request('filtro'), 'concesionario_id' => request('concesionario_id')])) }}"
                            class="px-3 py-2 text-sm text-gray-400 hover:text-white bg-gray-800 border border-gray-700 rounded-xl transition">✕</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-800 text-gray-400 text-sm uppercase">
                    <tr>
                        <th class="p-4">Nombre</th>
                        <th class="p-4 hidden sm:table-cell">Actividad económica</th>
                        <th class="p-4 hidden sm:table-cell">Monto interés a aprobar</th>
                        <th class="p-4 hidden md:table-cell">Teléfono</th>
                        <th class="p-4">Concesionario</th>
                        <th class="p-4 hidden lg:table-cell">Asesor</th>
                        <th class="p-4 hidden lg:table-cell w-40">Observaciones</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        @php $cfg = $estadoConfig[$lead->estado_gestion] ?? ['badge' => 'bg-gray-700 text-gray-300', 'label' => $lead->estado_gestion]; @endphp
                        <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition">
                            <td class="p-4">
                                <div class="font-medium">{{ $lead->full_name ?: 'Sin nombre' }}</div>
                                <div class="text-sm text-gray-400 md:hidden truncate">{{ $lead->observaciones ?: $lead->email }}</div>
                                <div class="text-xs text-gray-500 mt-0.5 md:hidden">
                                    @if($lead->phone_number)
                                        <a href="{{ $lead->whatsapp_url }}" target="_blank" rel="noopener" class="text-green-400 hover:text-green-300">{{ $lead->phone_number }}</a>
                                    @endif
                                </div>
                                <div class="text-xs mt-0.5 lg:hidden">
                                    @if($lead->asesorComercial)
                                        <span class="text-teal-400">Asesor: {{ $lead->asesorComercial->nombre }}</span>
                                    @else
                                        <span class="text-gray-600">Sin asesor</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 hidden sm:table-cell">{{ $lead->actividad_economica ?: '—' }}</td>
                            <td class="p-4 hidden sm:table-cell">{{ $lead->monto_interes_aprobar ?: '—' }}</td>
                            <td class="p-4 hidden md:table-cell">
                                @if($lead->phone_number)
                                    <a href="{{ $lead->whatsapp_url }}" target="_blank" rel="noopener" class="text-green-400 hover:text-green-300 hover:underline">{{ $lead->phone_number }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                {{ $lead->concesionario->nombre ?? 'Sin asignar' }}
                            </td>
                            <td class="p-4 hidden lg:table-cell whitespace-nowrap">
                                @if($lead->asesorComercial)
                                    <span class="text-teal-400">{{ $lead->asesorComercial->nombre }}</span>
                                @else
                                    <span class="text-gray-500">Sin asesor</span>
                                @endif
                            </td>
                            <td class="p-4 hidden lg:table-cell w-40 text-xs text-gray-400 truncate" title="{{ $lead->observaciones }}">{{ $lead->observaciones ?: '—' }}</td>
                            <td class="p-4">
                                @if($lead->vencido)
                                    <span class="text-xs bg-red-500/20 text-red-400 px-3 py-1 rounded-full">Vencido</span>
                                @else
                                    <span class="text-xs {{ $cfg['badge'] }} px-3 py-1 rounded-full">{{ $cfg['label'] }}</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('leads.show', $lead) }}"
                                        title="Ver"
                                        class="p-2.5 sm:p-1.5 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-300 hover:text-white transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </a>

                                    @can('update', $lead)
                                        <a href="{{ route('leads.edit', $lead) }}"
                                            title="Editar"
                                            class="p-2.5 sm:p-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 hover:text-blue-300 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                            </svg>
                                        </a>
                                    @endcan

                                    @can('reassign', $lead)
                                        <details class="relative">
                                            <summary class="list-none cursor-pointer p-2.5 sm:p-1.5 rounded-lg bg-amber-600/20 hover:bg-amber-600/40 text-amber-400 hover:text-amber-300 transition" title="Reasignar">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                </svg>
                                            </summary>
                                            <div class="absolute right-0 z-20 mt-2 w-64 bg-gray-800 border border-gray-700 rounded-xl p-4 shadow-xl">
                                                <form method="POST" action="{{ route('leads.reassign', $lead) }}" class="space-y-3">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div>
                                                        <label class="text-xs text-gray-400">Nuevo concesionario</label>
                                                        <select name="to_concesionario_id" required
                                                            class="w-full mt-1 bg-gray-900 border border-gray-700 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:border-blue-500">
                                                            @foreach($concesionarios as $c)
                                                                <option value="{{ $c->id }}" @selected($c->id === $lead->concesionario_id)>{{ $c->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="text-xs text-gray-400">Motivo (opcional)</label>
                                                        <textarea name="motivo" rows="2"
                                                            class="w-full mt-1 bg-gray-900 border border-gray-700 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:border-blue-500"></textarea>
                                                    </div>
                                                    <button type="submit"
                                                        class="w-full bg-blue-600 hover:bg-blue-700 rounded-lg py-1.5 text-sm font-medium transition">
                                                        Reasignar
                                                    </button>
                                                </form>
                                            </div>
                                        </details>
                                    @endcan

                                    @can('assignAsesor', $lead)
                                        @php $asesoresLead = $asesoresPorConcesionario->get($lead->concesionario_id, collect()); @endphp
                                        <details class="relative">
                                            <summary class="list-none cursor-pointer p-2.5 sm:p-1.5 rounded-lg bg-teal-600/20 hover:bg-teal-600/40 text-teal-400 hover:text-teal-300 transition" title="Asignar asesor">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            </summary>
                                            <div class="absolute right-0 z-20 mt-2 w-64 bg-gray-800 border border-gray-700 rounded-xl p-4 shadow-xl">
                                                @if($asesoresLead->isEmpty())
                                                    <p class="text-xs text-gray-500">Este concesionario no tiene asesores registrados todavía.</p>
                                                @else
                                                    <form method="POST" action="{{ route('leads.assign-asesor', $lead) }}" class="space-y-3">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div>
                                                            <label class="text-xs text-gray-400">Asesor</label>
                                                            <select name="asesor_comercial_id" required
                                                                class="w-full mt-1 bg-gray-900 border border-gray-700 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:border-blue-500">
                                                                @foreach($asesoresLead as $a)
                                                                    <option value="{{ $a->id }}" @selected($a->id === $lead->asesor_comercial_id)>{{ $a->nombre }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <button type="submit"
                                                            class="w-full bg-blue-600 hover:bg-blue-700 rounded-lg py-1.5 text-sm font-medium transition">
                                                            Asignar
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </details>
                                    @endcan

                                    @can('delete', $lead)
                                        <form method="POST" action="{{ route('leads.destroy', $lead) }}" onsubmit="return confirm('¿Eliminar este lead?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Eliminar"
                                                class="p-2.5 sm:p-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-400 hover:text-red-300 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-8 text-center text-gray-500">
                                {{ request()->anyFilled(['buscar', 'filtro', 'concesionario_id']) ? 'Sin resultados para tu búsqueda' : 'No hay leads registrados' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
            <div class="p-5 border-t border-gray-800">
                {{ $leads->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
