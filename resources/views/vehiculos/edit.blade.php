@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

```
<div class="flex items-center justify-between mb-8">

    <div>
        <h1 class="text-3xl font-bold">
            Editar Vehículo
        </h1>

        <p class="text-gray-400 mt-1">
            Actualizar información del vehículo
        </p>
    </div>

    <a href="{{ route('vehiculos.index') }}"
       class="bg-gray-800 hover:bg-gray-700 px-5 py-3 rounded-xl transition">

        Volver

    </a>

</div>

@if ($errors->any())

    <div class="mb-6 bg-red-500/10 border border-red-500/50 rounded-xl p-4">

        <p class="text-red-400 font-semibold mb-2">
            Errores:
        </p>

        <ul class="text-red-400 text-sm list-disc list-inside">

            @foreach ($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif

<form
    action="{{ route('vehiculos.update', $vehiculo) }}"
    method="POST"
    class="bg-gray-900 border border-gray-800 rounded-3xl p-8">

    @csrf
    @method('PUT')

    @include('vehiculos._form')

    <div class="mt-8 flex gap-3">

        <button
            type="submit"
            class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl">

            Guardar Cambios

        </button>

        <a
            href="{{ route('vehiculos.show', $vehiculo) }}"
            class="bg-gray-700 hover:bg-gray-600 px-6 py-3 rounded-xl">

            Cancelar

        </a>

    </div>

</form>
```

</div>

@endsection
