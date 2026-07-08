@extends('layouts.app')

@section('content')

@php
    $avatarColors = ['bg-blue-600','bg-purple-600','bg-green-600','bg-amber-600','bg-red-600','bg-teal-600','bg-pink-600','bg-indigo-600'];

    function clienteColor($nombre, $colors) {
        return $colors[abs(crc32($nombre)) % count($colors)];
    }

    function clienteInitials($nombre) {
        $words = array_filter(explode(' ', $nombre));
        $initials = '';
        foreach (array_slice(array_values($words), 0, 2) as $w) {
            $initials .= strtoupper($w[0]);
        }
        return $initials;
    }

    $estadoConfig = [
        'Nuevo'       => ['badge' => 'bg-blue-500/20 text-blue-400',   'label' => 'Nuevo'],
        'Contactado'  => ['badge' => 'bg-teal-500/20 text-teal-400',   'label' => 'Contactado'],
        'Negociacion' => ['badge' => 'bg-amber-500/20 text-amber-400', 'label' => 'Negociación'],
        'Vendido'     => ['badge' => 'bg-green-500/20 text-green-400', 'label' => 'Vendido'],
        'Perdido'     => ['badge' => 'bg-red-500/20 text-red-400',     'label' => 'Perdido'],
    ];
@endphp

{{-- ===================== VISTA MÓVIL ===================== --}}
<div class="lg:hidden space-y-5">

    {{-- Encabezado de sección --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold">Clientes</h1>
            <p class="text-gray-400 text-sm mt-0.5">Gestión de clientes y leads</p>
        </div>
        <a href="{{ route('clientes.create') }}"
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

    {{-- Stats 2×2 --}}
    <div class="grid grid-cols-2 gap-3">

        {{-- Total clientes --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 bg-blue-600/20 rounded-xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                @php $nuevosEsteMes = $clientes->filter(fn($c) => $c->created_at->isCurrentMonth())->count(); @endphp
                @if($nuevosEsteMes > 0)
                    <span class="text-xs font-medium text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full">+{{ $nuevosEsteMes }}</span>
                @endif
            </div>
            <p class="text-3xl font-bold">{{ $clientes->count() }}</p>
            <p class="text-gray-400 text-xs mt-1">Total clientes</p>
        </div>

        {{-- Leads nuevos --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 bg-amber-600/20 rounded-xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                    </svg>
                </div>
                @if(($totalLeadsNuevos ?? 0) > 0)
                    <span class="text-xs font-medium text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full">+{{ $totalLeadsNuevos ?? 0 }}</span>
                @endif
            </div>
            <p class="text-3xl font-bold">{{ $totalLeadsNuevos ?? 0 }}</p>
            <p class="text-gray-400 text-xs mt-1">Leads nuevos</p>
        </div>

        {{-- Ventas cerradas --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 bg-green-600/20 rounded-xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <span class="text-xs font-medium text-green-400 bg-green-500/10 px-2 py-0.5 rounded-full">este mes</span>
            </div>
            <p class="text-3xl font-bold">{{ $totalVentasCerradas ?? 0 }}</p>
            <p class="text-gray-400 text-xs mt-1">Ventas cerradas</p>
        </div>

        {{-- Seguimientos urgentes --}}
        @php $perdidos = $clientes->where('estado', 'Perdido')->count(); @endphp
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 bg-red-600/20 rounded-xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                @if($perdidos > 0)
                    <span class="text-xs font-medium text-red-400 bg-red-500/10 px-2 py-0.5 rounded-full">urgente</span>
                @endif
            </div>
            <p class="text-3xl font-bold">{{ $clientes->whereIn('estado', ['Negociacion'])->count() }}</p>
            <p class="text-gray-400 text-xs mt-1">Seguimientos</p>
        </div>

    </div>

    {{-- Búsqueda móvil --}}
    <form method="GET" action="{{ route('clientes.index') }}" class="flex gap-2">
        <div class="flex-1 relative">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z" />
            </svg>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar clientes..."
                class="w-full bg-gray-900 border border-gray-800 rounded-2xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
        </div>
        @if(request('buscar'))
            <a href="{{ route('clientes.index') }}"
                class="px-3 py-2.5 text-sm text-gray-400 bg-gray-900 border border-gray-800 rounded-2xl transition hover:text-white">✕</a>
        @endif
    </form>

    {{-- Lista de clientes --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-semibold text-gray-200">
                Clientes recientes
            </h2>
            <span class="text-xs text-gray-500">{{ $clientes->count() }} total</span>
        </div>

        <div class="space-y-2">
            @forelse($clientes as $cliente)
                @php
                    $color    = clienteColor($cliente->nombre, $avatarColors);
                    $initials = clienteInitials($cliente->nombre);
                    $cfg      = $estadoConfig[$cliente->estado] ?? ['badge' => 'bg-gray-700 text-gray-300', 'label' => $cliente->estado];
                @endphp
                <a href="{{ route('clientes.show', $cliente) }}"
                    class="flex items-center gap-3 bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3.5 hover:border-gray-700 transition active:scale-[.99]">
                    <div class="{{ $color }} w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-medium text-sm truncate">{{ $cliente->nombre }}</p>
                            <span class="text-xs {{ $cfg['badge'] }} px-2 py-0.5 rounded-full shrink-0">{{ $cfg['label'] }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 mt-0.5">
                            <p class="text-xs text-gray-500 truncate">{{ $cliente->vehiculo_interes ?: $cliente->email }}</p>
                            <p class="text-xs text-gray-600 shrink-0">{{ $cliente->updated_at->diffForHumans(null, true, true) }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-12 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-10 h-10 mx-auto mb-3 text-gray-700">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    No hay clientes registrados
                </div>
            @endforelse
        </div>
    </div>

    {{-- Acciones rápidas --}}
    <div>
        <h2 class="text-base font-semibold text-gray-200 mb-3">Acciones rápidas</h2>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('clientes.create') }}"
                class="flex items-center gap-3 bg-gray-900 border border-gray-800 hover:border-blue-500/40 rounded-2xl px-4 py-3.5 transition">
                <div class="w-8 h-8 bg-blue-600/20 rounded-xl flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-blue-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-200">Nuevo cliente</span>
            </a>
            <a href="{{ route('vehiculos.index') }}"
                class="flex items-center gap-3 bg-gray-900 border border-gray-800 hover:border-blue-500/40 rounded-2xl px-4 py-3.5 transition">
                <div class="w-8 h-8 bg-gray-700 rounded-xl flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-300">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-200">Ver vehículos</span>
            </a>
            <a href="{{ route('leads.index') }}"
                class="flex items-center gap-3 bg-gray-900 border border-gray-800 hover:border-blue-500/40 rounded-2xl px-4 py-3.5 transition">
                <div class="w-8 h-8 bg-purple-600/20 rounded-xl flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-purple-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-200">Ver leads</span>
            </a>
            <a href="{{ route('ventas.index') }}"
                class="flex items-center gap-3 bg-gray-900 border border-gray-800 hover:border-blue-500/40 rounded-2xl px-4 py-3.5 transition">
                <div class="w-8 h-8 bg-green-600/20 rounded-xl flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-green-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-200">Ver ventas</span>
            </a>
        </div>
    </div>

</div>

{{-- ===================== VISTA DESKTOP ===================== --}}
<div class="hidden lg:block">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">Clientes</h1>
            <p class="text-gray-400 mt-1">Gestión de clientes y leads</p>
        </div>
        @include('partials._boton-crear', ['href' => route('clientes.create'), 'texto' => 'Nuevo Cliente'])
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/50 rounded-xl p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <div class="bg-[#0f172a]/40 border border-slate-800/60 rounded-xl p-5 backdrop-blur-sm">
            <div class="flex items-start space-x-4">
                <div class="text-blue-500 pt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10">
                        <path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.363-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 14.434 14.434 0 0 0 5.234-1.103.75.75 0 0 0 .422-.674v-.118a5.25 5.25 0 0 0-10.255-1.785 7.11 7.11 0 0 1 4.833 2.576Z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-gray-400 text-sm font-medium leading-none mb-1">Total Clientes</p>
                    <h2 class="text-4xl font-bold text-white tracking-tight mt-2">{{ $clientes->count() }}</h2>
                </div>
            </div>
        </div>

        <div class="bg-[#0f172a]/40 border border-slate-800/60 rounded-xl p-5 backdrop-blur-sm">
            <div class="flex items-start space-x-4">
                <div class="text-green-500 pt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm0 14.25a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0-2.25a2.25 2.25 0 1 0 0-4 2.25 2.25 0 0 0 0 4Z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-gray-400 text-sm font-medium leading-none mb-1">Leads Nuevos</p>
                    <h2 class="text-4xl font-bold text-white tracking-tight mt-2">{{ $totalLeadsNuevos ?? 0 }}</h2>
                </div>
            </div>
        </div>

        <div class="bg-[#0f172a]/40 border border-slate-800/60 rounded-xl p-5 backdrop-blur-sm">
            <div class="flex items-start space-x-4">
                <div class="text-yellow-500 pt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10">
                        <path d="M11.644 3.166a1 1 0 0 1 1.212 0l5.625 4.375a1 1 0 0 1 .375.78v11.429a1 1 0 0 1-1 1H6.144a1 1 0 0 1-1-1V8.32a1 1 0 0 1 .375-.78l5.625-4.375Z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-gray-400 text-sm font-medium leading-none mb-1">Ventas Cerradas</p>
                    <h2 class="text-4xl font-bold text-white tracking-tight mt-2">{{ $totalVentasCerradas ?? 0 }}</h2>
                </div>
            </div>
        </div>

    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">

        <div class="p-5 border-b border-gray-800 flex flex-wrap gap-3 justify-between items-center">
            <h2 class="text-xl font-semibold">
                Lista de Clientes
                <span class="text-sm font-normal text-gray-400 ml-1">({{ $clientes->count() }} resultados)</span>
            </h2>
            <form method="GET" action="{{ route('clientes.index') }}" class="flex gap-2">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                    placeholder="Buscar por nombre, email o teléfono..."
                    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm w-64 focus:outline-none focus:border-blue-500">
                @if(request('buscar'))
                    <a href="{{ route('clientes.index') }}"
                        class="px-3 py-2 text-sm text-gray-400 hover:text-white bg-gray-800 border border-gray-700 rounded-xl transition">✕</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-800 text-gray-400 text-sm uppercase">
                    <tr>
                        <th class="p-4">Cliente</th>
                        <th class="p-4 hidden sm:table-cell">Teléfono</th>
                        <th class="p-4 hidden sm:table-cell">Ciudad</th>
                        <th class="p-4 hidden md:table-cell">Vehículo</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clientes as $cliente)
                        @php $cfg = $estadoConfig[$cliente->estado] ?? ['badge' => 'bg-gray-700 text-gray-300', 'label' => $cliente->estado]; @endphp
                        <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition">
                            <td class="p-4">
                                <div class="font-medium">{{ $cliente->nombre }}</div>
                                <div class="text-sm text-gray-400">{{ $cliente->email }}</div>
                                <div class="sm:hidden text-xs text-gray-500 mt-0.5">{{ $cliente->telefono }}</div>
                            </td>
                            <td class="p-4 hidden sm:table-cell">{{ $cliente->telefono }}</td>
                            <td class="p-4 hidden sm:table-cell">{{ $cliente->ciudad }}</td>
                            <td class="p-4 hidden md:table-cell">{{ $cliente->vehiculo_interes }}</td>
                            <td class="p-4">
                                <span class="text-xs {{ $cfg['badge'] }} px-3 py-1 rounded-full">{{ $cfg['label'] }}</span>
                            </td>
                            <td class="p-4">
                                @include('partials._acciones', [
                                    'modelo' => $cliente,
                                    'ruta'   => 'clientes',
                                    'label'  => 'cliente',
                                ])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

</div>

@endsection
