@extends('layouts.app')

@section('content')

<div class="max-w-5xl mx-auto">

    <h1 class="text-3xl font-bold mb-8">
        Editar Asesor Comercial
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

    <form
        method="POST"
        action="{{ route('asesores.update', $asesor) }}"
        class="bg-gray-900 border border-gray-800 rounded-3xl p-8">

        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="block text-sm text-gray-400 mb-2">Cédula</label>
                <input type="text" name="cedula" value="{{ old('cedula', $asesor->cedula) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-2">Nombre</label>
                <input type="text" name="nombre" value="{{ old('nombre', $asesor->nombre) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-2">Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono', $asesor->telefono) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-2">Concesionario al que pertenece</label>
                <select name="concesionario_id"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
                    <option value="">Selecciona uno</option>
                    @foreach($concesionarios as $c)
                        <option value="{{ $c->id }}" @selected((int) old('concesionario_id', $asesor->concesionario_id) === $c->id)>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="mt-10 flex gap-4">

            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl font-medium transition">
                Actualizar Asesor
            </button>

            <a
                href="{{ route('asesores.index') }}"
                class="bg-gray-700 hover:bg-gray-600 px-6 py-3 rounded-xl transition">
                Cancelar
            </a>

        </div>

    </form>

</div>

@endsection
