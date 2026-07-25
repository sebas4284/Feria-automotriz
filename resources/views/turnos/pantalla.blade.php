<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turnos — AutoFeria CRM</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center p-10">

    <div class="w-full max-w-5xl text-center">

        <img src="{{ asset('images/expocarshow-logo-white.png') }}" alt="Expocar Show" class="h-14 mx-auto mb-12 opacity-80">

        @if($ultimoCliente)
            <p class="text-3xl lg:text-4xl text-gray-400 mb-4">Cliente</p>
            <p class="text-6xl lg:text-8xl font-bold mb-16 break-words">{{ $ultimoCliente->nombre }}</p>

            <p class="text-3xl lg:text-4xl text-gray-400 mb-4">Lo atiende</p>
            <p class="text-7xl lg:text-9xl font-extrabold text-blue-400 break-words">
                {{ $ultimoCliente->concesionario->nombre ?? 'Sin asignar' }}
            </p>
        @else
            <p class="text-4xl lg:text-6xl text-gray-500 font-semibold">
                Esperando el primer cliente del día
            </p>
        @endif

    </div>

    <script>
        setTimeout(() => window.location.reload(), 5000);
    </script>

</body>
</html>
