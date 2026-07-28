@extends('layouts.app')

@section('content')

    <div class="max-w-4xl mx-auto">

        <div class="flex items-center justify-between mb-8">

            <div>
                <h1 class="text-3xl font-bold">
                    Nuevo Cliente
                </h1>

                <p class="text-gray-400 mt-1">
                    Registrar nuevo lead o cliente
                </p>
            </div>

            <a href="{{ route('clientes.index') }}" class="bg-gray-800 hover:bg-gray-700 px-5 py-3 rounded-xl transition">
                Volver
            </a>

        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/50 rounded-xl p-4">
                <p class="text-red-400 font-semibold mb-2">Errores:</p>
                <ul class="text-red-400 text-sm list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('clientes.store') }}" method="POST"
            class="bg-gray-900 border border-gray-800 rounded-2xl p-8"
            x-data="{ cita: {{ old('cita') ? 'true' : 'false' }} }">

            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block mb-2 text-sm text-gray-400">
                        Nombre
                    </label>

                    <input type="text" name="nombre" value="{{ old('nombre') }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
                        required>
                    @error('nombre')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-400">
                        Teléfono
                    </label>

                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('telefono') border-red-500 @enderror"
                        required>
                    @error('telefono')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="cita">
                    <label class="block mb-2 text-sm text-gray-400">
                        Concesionario asignado
                    </label>

                    <select name="concesionario_id"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('concesionario_id') border-red-500 @enderror">
                        <option value="">Sin asignar</option>
                        @foreach($concesionarios as $c)
                            <option value="{{ $c->id }}" @selected((int) old('concesionario_id') === $c->id)>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                    @error('concesionario_id')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="!cita" x-cloak>
                    <label class="block mb-2 text-sm text-gray-400">
                        Concesionario asignado
                    </label>
                    <p class="text-sm text-gray-500 bg-gray-800/60 border border-gray-700 rounded-xl px-4 py-3">
                        Quedará pendiente en Turnos para que lo asignen arrastrándolo al concesionario correspondiente.
                    </p>
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-400">
                        ¿Cómo se enteró?
                    </label>

                    <select name="medio_entero"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('medio_entero') border-red-500 @enderror">
                        <option value="">Sin especificar</option>
                        @foreach($medios as $medio)
                            <option value="{{ $medio }}" @selected(old('medio_entero') === $medio)>{{ $medio }}</option>
                        @endforeach
                    </select>
                    @error('medio_entero')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 md:pt-8">
                    <input type="checkbox" name="cita" id="cita" value="1" x-model="cita"
                        class="w-5 h-5 rounded bg-gray-800 border-gray-700 text-blue-600 focus:ring-blue-500">
                    <label for="cita" class="text-sm text-gray-400">
                        ¿Tiene cita agendada?
                    </label>
                </div>

            </div>

            <div class="mt-6">

                <label class="block mb-2 text-sm text-gray-400">
                    Observaciones
                </label>

                <textarea name="observaciones" rows="5"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('observaciones') }}</textarea>
                @error('observaciones')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror

            </div>

            <div class="mt-8">

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl font-medium transition">
                    Guardar Cliente
                </button>

            </div>

        </form>

    </div>

@endsection