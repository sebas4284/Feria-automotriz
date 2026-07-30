<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de llegada — AutoFeria CRM</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-950 text-white min-h-screen p-10">

    <div class="w-full max-w-6xl mx-auto">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold">Orden de llegada</h1>
                <p class="text-lg lg:text-xl text-gray-400 mt-1">
                    {{ now()->translatedFormat('l d \d\e F Y') }} / {{ now()->format('h:i A') }}
                </p>
            </div>
            <img src="{{ asset('images/expocarshow-logo-white.png') }}" alt="Expocar Show" class="h-10 lg:h-14">
        </div>

        @if($filaCompleta->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($filaCompleta as $turno)
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl px-5 py-4 flex items-center justify-between gap-3">
                        <span class="font-semibold text-lg lg:text-xl truncate">{{ $turno->concesionario->nombre }}</span>
                        <span class="text-xl lg:text-2xl font-bold text-blue-400 shrink-0">#{{ $ordenLlegada[$turno->concesionario_id] }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-4xl lg:text-6xl text-gray-500 font-semibold text-center mt-20">
                Ningún concesionario ha marcado llegada hoy
            </p>
        @endif

    </div>

    <script>
        setTimeout(() => window.location.reload(), 5000);
    </script>

</body>
</html>
