@extends('layouts.app')

@section('content')

{{-- ===================== VISTA MÓVIL ===================== --}}
<div class="lg:hidden space-y-5">

    {{-- Encabezado --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold">Asesores Comerciales</h1>
            <p class="text-gray-400 text-sm mt-0.5">Gestión de asesores por concesionario</p>
        </div>
        <a href="{{ route('asesores.create') }}"
            class="w-11 h-11 bg-blue-600 hover:bg-blue-700 rounded-2xl flex items-center justify-center transition shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-2xl px-4 py-3 text-green-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Contador --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-blue-600/20 rounded-xl flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </div>
            <span class="text-sm text-gray-300">Total asesores</span>
        </div>
        <span class="text-xl font-bold text-blue-400">{{ $asesores->count() }}</span>
    </div>

    {{-- Lista de tarjetas --}}
    <div class="space-y-2">
        @forelse($asesores as $asesor)
            <div class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3.5 hover:border-gray-700 transition">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm truncate">{{ $asesor->nombre }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">CC {{ $asesor->cedula }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 mb-3 pl-13">
                    <div>
                        <p class="text-xs text-gray-600">Teléfono</p>
                        <p class="text-xs text-gray-300 font-medium truncate">{{ $asesor->telefono ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600">Concesionario</p>
                        <p class="text-xs text-gray-300 font-medium truncate">{{ $asesor->concesionario->nombre ?? 'Sin asignar' }}</p>
                    </div>
                </div>

                <div class="flex gap-2 pt-3 border-t border-gray-800">
                    <a href="{{ route('asesores.show', $asesor) }}"
                        class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-xl bg-gray-700 hover:bg-gray-600 text-gray-300 text-xs transition">
                        Ver
                    </a>
                    @can('update', $asesor)
                        <a href="{{ route('asesores.edit', $asesor) }}"
                            class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-xl bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 text-xs transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                            </svg>
                            Editar
                        </a>
                    @endcan
                    @can('delete', $asesor)
                        <form action="{{ route('asesores.destroy', $asesor) }}" method="POST"
                            onsubmit="return confirm('¿Eliminar este asesor?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="flex items-center justify-center gap-1.5 px-4 py-1.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                Eliminar
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-10 h-10 mx-auto mb-3 text-gray-700">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                No hay asesores registrados
            </div>
        @endforelse
    </div>

</div>

{{-- ===================== VISTA DESKTOP ===================== --}}
<div class="hidden lg:block">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Asesores Comerciales</h1>
            <p class="text-gray-400">Gestión de asesores por concesionario</p>
        </div>
        @include('partials._boton-crear', ['href' => route('asesores.create'), 'texto' => 'Nuevo Asesor'])
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/50 rounded-xl p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-800">
                <tr>
                    <th class="p-4 text-left">Nombre</th>
                    <th class="p-4 text-left hidden sm:table-cell">Cédula</th>
                    <th class="p-4 text-left hidden sm:table-cell">Teléfono</th>
                    <th class="p-4 text-left hidden md:table-cell">Concesionario</th>
                    <th class="p-4 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asesores as $asesor)
                    <tr class="border-t border-gray-800 hover:bg-gray-800/40 transition">
                        <td class="p-4">
                            <div>
                                <p class="font-medium">{{ $asesor->nombre }}</p>
                                <p class="text-xs text-gray-500 sm:hidden mt-0.5">CC {{ $asesor->cedula }}</p>
                            </div>
                        </td>
                        <td class="p-4 hidden sm:table-cell">{{ $asesor->cedula }}</td>
                        <td class="p-4 hidden sm:table-cell">{{ $asesor->telefono }}</td>
                        <td class="p-4 hidden md:table-cell">{{ $asesor->concesionario->nombre ?? 'Sin asignar' }}</td>
                        <td class="p-4">
                            @include('partials._acciones', [
                                'modelo'      => $asesor,
                                'ruta'        => 'asesores',
                                'label'       => 'asesor',
                                'sinEditar'   => auth()->user()->cannot('update', $asesor),
                                'sinEliminar' => auth()->user()->cannot('delete', $asesor),
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

@include('partials._auto-refresh')
@endsection
