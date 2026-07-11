<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AutoFeria CRM</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-950 text-white">

    <div class="flex min-h-screen">

        @php
            $navItems = [
                ['href' => '/dashboard',                  'label' => 'Dashboard',      'match' => 'dashboard',      'roles' => ['admin', 'concesionario', 'asesor'],
                 'icon' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
                ['href' => route('clientes.index'),       'label' => 'Clientes',       'match' => 'clientes*',      'roles' => ['admin', 'concesionario'],
                 'icon' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
                ['href' => route('leads.index'),          'label' => 'Leads',          'match' => 'leads*',         'roles' => ['admin', 'concesionario', 'asesor'],
                 'icon' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941'],
                ['href' => route('vehiculos.index'),      'label' => 'Vehículos',      'match' => 'vehiculos*',     'roles' => ['admin', 'concesionario', 'asesor'],
                 'icon' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
                ['href' => route('concesionarios.index'), 'label' => 'Concesionarios', 'match' => 'concesionarios*', 'roles' => ['admin'],
                 'icon' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'],
                ['href' => route('ventas.index'),         'label' => 'Ventas',         'match' => 'ventas*',        'roles' => ['admin', 'concesionario'],
                 'icon' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z'],
                ['href' => route('asesores.index'),       'label' => 'Asesores',       'match' => 'asesores*',      'roles' => ['admin', 'concesionario'],
                 'icon' => 'M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
                ['href' => route('rifa.index'),           'label' => 'Rifa',           'match' => 'rifa*',          'roles' => ['admin', 'staff'],
                 'icon' => 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z'],
                ['href' => route('turnos.index'),         'label' => 'Turnos',         'match' => 'turnos*',        'roles' => ['admin', 'staff'],
                 'icon' => 'M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['href' => route('estadisticas.index'),   'label' => 'Estadísticas',   'match' => 'estadisticas*',  'roles' => ['admin', 'concesionario'],
                 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
                ['href' => route('usuarios.index'),       'label' => 'Usuarios',       'match' => 'usuarios*',      'roles' => ['admin'],
                 'icon' => 'M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6.75-3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z'],
            ];

            $navItems = array_values(array_filter(
                $navItems,
                fn ($item) => in_array(Auth::user()->rol, $item['roles'], true)
            ));
        @endphp

        {{-- Sidebar desktop (estático, en flujo normal del documento) --}}
        <aside class="hidden lg:block w-64 min-h-screen bg-gray-900 border-r border-gray-800 shrink-0">
            <div class="flex flex-col h-full">

            <div class="p-5 border-b border-gray-800 flex items-center justify-between shrink-0">
                <a href="/">
                    <img src="{{ asset('images/expocarshow-logo-white.png') }}" alt="Expocar Show" class="h-8 w-auto">
                </a>
            </div>

            <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
                @foreach($navItems as $item)
                    <a href="{{ $item['href'] }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition text-sm font-medium
                            {{ request()->is($item['match']) ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            </div>
        </aside>

        {{-- Main --}}
        <main class="flex-1 min-w-0 flex flex-col">

            {{-- Header móvil --}}
            <header class="lg:hidden bg-gray-900 border-b border-gray-800 px-4 py-3 sticky top-0 z-30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <a href="/">
                            <img src="{{ asset('images/expocarshow-logo-white.png') }}" alt="Expocar Show" class="h-6 w-auto">
                        </a>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <button class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-800 text-gray-400 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                </svg>
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border border-gray-900"></span>
                            </button>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center font-bold text-sm select-none">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-800 text-gray-400 hover:text-red-400 transition" title="Cerrar sesión">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            {{-- Header desktop --}}
            <header class="hidden lg:block bg-gray-900 border-b border-gray-800 px-6 py-4 sticky top-0 z-30">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-bold truncate">
                        @if(request()->is('dashboard'))           Dashboard
                        @elseif(request()->is('clientes*'))       Clientes
                        @elseif(request()->is('leads*'))          Leads
                        @elseif(request()->is('vehiculos*'))      Vehículos
                        @elseif(request()->is('ventas*'))         Ventas
                        @elseif(request()->is('asesores*'))       Asesores
                        @elseif(request()->is('rifa*'))           Rifa
                        @elseif(request()->is('concesionarios*')) Concesionarios
                        @elseif(request()->is('estadisticas*'))   Estadísticas
                        @elseif(request()->is('usuarios*'))       Usuarios
                        @else AutoFeria CRM
                        @endif
                    </h2>
                    <div class="flex items-center gap-3 shrink-0">
                        <input type="text" placeholder="Buscar..."
                            class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm w-56 focus:outline-none focus:border-blue-500">
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-sm font-medium leading-none">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-green-400 mt-0.5">En línea</p>
                            </div>
                            <div class="relative shrink-0">
                                <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-gray-900 rounded-full"></span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-1.5 bg-gray-800 hover:bg-red-500/20 text-gray-400 hover:text-red-400 px-3 py-2 rounded-xl text-sm transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>
                                Salir
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <section class="flex-1 p-4 lg:p-6 pb-24 lg:pb-6">
                @yield('content')
            </section>

        </main>

    </div>

    {{-- Bottom nav (solo móvil) --}}
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-gray-900 border-t border-gray-800 z-50">
        <div class="flex">

            @if(in_array(Auth::user()->rol, ['admin', 'concesionario', 'asesor']))
            <a href="/dashboard"
                class="flex-1 flex flex-col items-center justify-center py-3 gap-1 text-xs font-medium transition relative
                    {{ request()->is('dashboard') ? 'text-blue-500' : 'text-gray-500 hover:text-gray-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Dashboard
                @if(request()->is('dashboard'))
                    <span class="absolute bottom-0 w-8 h-0.5 bg-blue-500 rounded-full"></span>
                @endif
            </a>
            @endif

            @if(in_array(Auth::user()->rol, ['admin', 'concesionario']))
            <a href="{{ route('clientes.index') }}"
                class="flex-1 flex flex-col items-center justify-center py-3 gap-1 text-xs font-medium transition relative
                    {{ request()->is('clientes*') ? 'text-blue-500' : 'text-gray-500 hover:text-gray-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                Clientes
                @if(request()->is('clientes*'))
                    <span class="absolute bottom-0 w-8 h-0.5 bg-blue-500 rounded-full"></span>
                @endif
            </a>
            @endif

            @if(in_array(Auth::user()->rol, ['admin', 'concesionario', 'asesor']))
            <a href="{{ route('leads.index') }}"
                class="flex-1 flex flex-col items-center justify-center py-3 gap-1 text-xs font-medium transition relative
                    {{ request()->is('leads*') ? 'text-blue-500' : 'text-gray-500 hover:text-gray-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                </svg>
                Leads
                @if(request()->is('leads*'))
                    <span class="absolute bottom-0 w-8 h-0.5 bg-blue-500 rounded-full"></span>
                @endif
            </a>
            @endif

            @if(in_array(Auth::user()->rol, ['admin', 'concesionario', 'asesor']))
            <a href="{{ route('vehiculos.index') }}"
                class="flex-1 flex flex-col items-center justify-center py-3 gap-1 text-xs font-medium transition relative
                    {{ request()->is('vehiculos*') ? 'text-blue-500' : 'text-gray-500 hover:text-gray-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
                Vehículos
                @if(request()->is('vehiculos*'))
                    <span class="absolute bottom-0 w-8 h-0.5 bg-blue-500 rounded-full"></span>
                @endif
            </a>
            @endif

            @if(in_array(Auth::user()->rol, ['admin', 'concesionario']))
            <a href="{{ route('ventas.index') }}"
                class="flex-1 flex flex-col items-center justify-center py-3 gap-1 text-xs font-medium transition relative
                    {{ request()->is('ventas*') ? 'text-blue-500' : 'text-gray-500 hover:text-gray-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
                Ventas
                @if(request()->is('ventas*'))
                    <span class="absolute bottom-0 w-8 h-0.5 bg-blue-500 rounded-full"></span>
                @endif
            </a>
            @endif

            @if(in_array(Auth::user()->rol, ['admin', 'concesionario']))
            <a href="{{ route('estadisticas.index') }}"
                class="flex-1 flex flex-col items-center justify-center py-3 gap-1 text-xs font-medium transition relative
                    {{ request()->is('estadisticas*') ? 'text-blue-500' : 'text-gray-500 hover:text-gray-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                Stats
                @if(request()->is('estadisticas*'))
                    <span class="absolute bottom-0 w-8 h-0.5 bg-blue-500 rounded-full"></span>
                @endif
            </a>
            @endif

            @if(in_array(Auth::user()->rol, ['admin', 'staff']))
            <a href="{{ route('turnos.index') }}"
                class="flex-1 flex flex-col items-center justify-center py-3 gap-1 text-xs font-medium transition relative
                    {{ request()->is('turnos*') ? 'text-blue-500' : 'text-gray-500 hover:text-gray-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Turnos
                @if(request()->is('turnos*'))
                    <span class="absolute bottom-0 w-8 h-0.5 bg-blue-500 rounded-full"></span>
                @endif
            </a>
            @endif

            @if(in_array(Auth::user()->rol, ['admin', 'staff']))
            <a href="{{ route('rifa.index') }}"
                class="flex-1 flex flex-col items-center justify-center py-3 gap-1 text-xs font-medium transition relative
                    {{ request()->is('rifa*') ? 'text-blue-500' : 'text-gray-500 hover:text-gray-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                </svg>
                Rifa
                @if(request()->is('rifa*'))
                    <span class="absolute bottom-0 w-8 h-0.5 bg-blue-500 rounded-full"></span>
                @endif
            </a>
            @endif

        </div>
    </nav>

</body>

</html>