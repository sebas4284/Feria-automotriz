@extends('layouts.app')

@section('content')

    <div class="max-w-4xl mx-auto">

        <h1 class="text-3xl font-bold mb-6">
            Nuevo Asesor Comercial
        </h1>

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

        <form method="POST" action="{{ route('asesores.store') }}"
            class="bg-gray-900 border border-gray-800 rounded-3xl p-8">

            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block text-sm text-gray-400 mb-2">Cédula</label>
                    <input type="text" name="cedula" value="{{ old('cedula') }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-2">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-2">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-2">Concesionario al que pertenece</label>
                    <select name="concesionario_id"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
                        <option value="">Selecciona uno</option>
                        @foreach($concesionarios as $c)
                            <option value="{{ $c->id }}" @selected((int) old('concesionario_id') === $c->id)>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <button class="mt-6 bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl">
                Guardar
            </button>

        </form>

    </div>

@endsection
