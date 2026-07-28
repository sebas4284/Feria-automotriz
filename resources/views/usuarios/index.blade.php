@extends('layouts.app')

@section('content')

@php
    $rolConfig = [
        'admin'         => ['badge' => 'bg-purple-500/20 text-purple-400', 'label' => 'Admin'],
        'concesionario' => ['badge' => 'bg-blue-500/20 text-blue-400',     'label' => 'Concesionario'],
        'asesor'        => ['badge' => 'bg-teal-500/20 text-teal-400',     'label' => 'Asesor'],
        'staff'         => ['badge' => 'bg-amber-500/20 text-amber-400',   'label' => 'Staff'],
        'porteria'      => ['badge' => 'bg-cyan-500/20 text-cyan-400',     'label' => 'Portería'],
    ];
@endphp

<div x-data="liveSearch()">

{{-- ===================== VISTA MÓVIL ===================== --}}
<div class="lg:hidden space-y-5">

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold">Usuarios</h1>
            <p class="text-gray-400 text-sm mt-0.5">Gestión de cuentas de acceso</p>
        </div>
        <a href="{{ route('usuarios.create') }}"
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

    <input type="text" x-model="q" placeholder="Buscar por nombre, email, rol o concesionario..."
        class="w-full bg-gray-900 border border-gray-800 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">

    <div class="space-y-2">
        @forelse($usuarios as $usuario)
            <div x-show="matches($el)"
                data-search="{{ mb_strtolower($usuario->name.' '.$usuario->email.' '.$rolConfig[$usuario->rol]['label'].' '.($usuario->concesionario->nombre ?? $usuario->asesorComercial?->concesionario?->nombre ?? '')) }}"
                class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3.5">
                <div class="flex items-center justify-between gap-2 mb-1">
                    <p class="font-semibold text-sm truncate">{{ $usuario->name }}</p>
                    <span class="text-xs {{ $rolConfig[$usuario->rol]['badge'] }} px-2 py-0.5 rounded-full shrink-0">
                        {{ $rolConfig[$usuario->rol]['label'] }}
                    </span>
                </div>
                <p class="text-xs text-gray-500">{{ $usuario->email }}</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $usuario->concesionario->nombre ?? $usuario->asesorComercial?->concesionario?->nombre ?? 'Sin asignar' }}
                </p>

                <div class="flex gap-2 pt-3 mt-3 border-t border-gray-800">
                    <a href="{{ route('usuarios.edit', $usuario) }}"
                        class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-xl bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 text-xs transition">
                        Editar
                    </a>
                    <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST"
                        onsubmit="return confirm('¿Eliminar este usuario?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="flex items-center justify-center gap-1.5 px-4 py-1.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs transition">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-500">No hay usuarios registrados</div>
        @endforelse
        <div x-show="q.trim() !== '' && visibleCount === 0" class="text-center py-12 text-gray-500">
            Sin resultados para tu búsqueda
        </div>
    </div>

</div>

{{-- ===================== VISTA DESKTOP ===================== --}}
<div class="hidden lg:block">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Usuarios</h1>
            <p class="text-gray-400">Gestión de cuentas de acceso</p>
        </div>
        @include('partials._boton-crear', ['href' => route('usuarios.create'), 'texto' => 'Nuevo Usuario'])
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/50 rounded-xl p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden">
        <div class="p-5 border-b border-gray-800 flex justify-between items-center">
            <h2 class="text-xl font-semibold">
                Lista de Usuarios
                <span class="text-sm font-normal text-gray-400 ml-2">({{ $usuarios->count() }} resultados)</span>
            </h2>
            <input type="text" x-model="q" placeholder="Buscar por nombre, email, rol o concesionario..."
                class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm w-72 focus:outline-none focus:border-blue-500">
        </div>
        <table class="w-full">
            <thead class="bg-gray-800">
                <tr>
                    <th class="p-4 text-left">Nombre</th>
                    <th class="p-4 text-left hidden sm:table-cell">Email</th>
                    <th class="p-4 text-left">Rol</th>
                    <th class="p-4 text-left hidden md:table-cell">Concesionario</th>
                    <th class="p-4 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $usuario)
                    <tr x-show="matches($el)"
                        data-search="{{ mb_strtolower($usuario->name.' '.$usuario->email.' '.$rolConfig[$usuario->rol]['label'].' '.($usuario->concesionario->nombre ?? $usuario->asesorComercial?->concesionario?->nombre ?? '')) }}"
                        class="border-t border-gray-800 hover:bg-gray-800/40 transition">
                        <td class="p-4">
                            <p class="font-medium">{{ $usuario->name }}</p>
                            <p class="text-xs text-gray-500 sm:hidden mt-0.5">{{ $usuario->email }}</p>
                        </td>
                        <td class="p-4 hidden sm:table-cell">{{ $usuario->email }}</td>
                        <td class="p-4">
                            <span class="text-xs {{ $rolConfig[$usuario->rol]['badge'] }} px-3 py-1 rounded-full">
                                {{ $rolConfig[$usuario->rol]['label'] }}
                            </span>
                        </td>
                        <td class="p-4 hidden md:table-cell">{{ $usuario->concesionario->nombre ?? $usuario->asesorComercial?->concesionario?->nombre ?? '—' }}</td>
                        <td class="p-4">
                            @include('partials._acciones', [
                                'modelo'   => $usuario,
                                'ruta'     => 'usuarios',
                                'label'    => 'usuario',
                                'sinVer'   => true,
                            ])
                        </td>
                    </tr>
                @endforeach
                <tr x-show="q.trim() !== '' && visibleCount === 0">
                    <td colspan="5" class="text-center p-8 text-gray-400">Sin resultados para tu búsqueda</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

</div>

@endsection
