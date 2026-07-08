@extends('layouts.app')

@section('content')

    <div class="max-w-4xl mx-auto">

        <div class="flex items-center justify-between mb-8">

            <div>
                <h1 class="text-3xl font-bold">
                    Editar Cliente
                </h1>

                <p class="text-gray-400 mt-1">
                    Actualizar información del cliente
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

        <form action="{{ route('clientes.update', $cliente) }}" method="POST"
            class="bg-gray-900 border border-gray-800 rounded-2xl p-8">

            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block mb-2 text-sm text-gray-400">
                        Nombre
                    </label>

                    <input type="text" name="nombre" value="{{ old('nombre', $cliente->nombre) }}"
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

                    <input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono) }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('telefono')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-400">
                        Email
                    </label>

                    <input type="email" name="email" value="{{ old('email', $cliente->email) }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-400">
                        Ciudad
                    </label>

                    <input type="text" name="ciudad" value="{{ old('ciudad', $cliente->ciudad) }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('ciudad')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-400">
                        Vehículo de interés
                    </label>

                    <input type="text" name="vehiculo_interes" value="{{ old('vehiculo_interes', $cliente->vehiculo_interes) }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('vehiculo_interes')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-400">
                        Presupuesto
                    </label>

                    <input type="number" name="presupuesto" value="{{ old('presupuesto', $cliente->presupuesto) }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500" step="0.01">
                    @error('presupuesto')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="mt-6">
                <label class="block mb-2 text-sm text-gray-400">
                    Estado
                </label>

                <select name="estado" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Nuevo" @selected(old('estado', $cliente->estado) === 'Nuevo')>Nuevo</option>
                    <option value="Contactado" @selected(old('estado', $cliente->estado) === 'Contactado')>Contactado</option>
                    <option value="Negociacion" @selected(old('estado', $cliente->estado) === 'Negociacion')>Negociación</option>
                    <option value="Vendido" @selected(old('estado', $cliente->estado) === 'Vendido')>Vendido</option>
                    <option value="Perdido" @selected(old('estado', $cliente->estado) === 'Perdido')>Perdido</option>
                </select>
                @error('estado')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">

                <label class="block mb-2 text-sm text-gray-400">
                    Observaciones
                </label>

                <textarea name="observaciones" rows="5"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('observaciones', $cliente->observaciones) }}</textarea>
                @error('observaciones')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror

            </div>

            <div class="mt-8 flex gap-3">

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl font-medium transition">
                    Guardar Cambios
                </button>

                <a href="{{ route('clientes.show', $cliente) }}" class="bg-gray-700 hover:bg-gray-600 px-6 py-3 rounded-xl font-medium transition">
                    Cancelar
                </a>

            </div>

        </form>

    </div>

@endsection
