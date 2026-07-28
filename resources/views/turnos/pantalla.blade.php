<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turnos — AutoFeria CRM</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-950 text-white min-h-screen p-10">

    <div class="w-full max-w-5xl mx-auto">

        <div class="flex items-center justify-between mb-8">
            <p class="text-2xl lg:text-3xl text-gray-400">
                {{ now()->translatedFormat('l d \d\e F Y') }} / {{ now()->format('h:i A') }}
            </p>
            <img src="{{ asset('images/expocarshow-logo-white.png') }}" alt="Expocar Show" class="h-10 lg:h-14">
        </div>

        @if($asignaciones->isNotEmpty())
            <div class="rounded-3xl overflow-hidden border border-gray-800">
                <div class="grid grid-cols-2 bg-gray-800 text-xl lg:text-2xl font-semibold">
                    <div class="p-5">Concesionario</div>
                    <div class="p-5">Cliente</div>
                </div>
                @foreach($asignaciones as $i => $asignacion)
                    <div class="grid grid-cols-2 border-t border-gray-800 {{ $i === 0 ? 'bg-blue-600 text-white' : 'bg-gray-900 text-gray-200' }}">
                        <div class="p-5 font-bold truncate {{ $i === 0 ? 'text-4xl lg:text-5xl' : 'text-2xl lg:text-3xl' }}">
                            {{ $asignacion->concesionario->nombre ?? 'Sin asignar' }}
                        </div>
                        <div class="p-5 truncate {{ $i === 0 ? 'text-4xl lg:text-5xl font-bold' : 'text-2xl lg:text-3xl' }}">
                            {{ $asignacion->nombre }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-4xl lg:text-6xl text-gray-500 font-semibold text-center mt-20">
                Esperando el primer cliente del día
            </p>
        @endif

    </div>

    <script>
        setTimeout(() => window.location.reload(), 5000);
    </script>

</body>
</html>
