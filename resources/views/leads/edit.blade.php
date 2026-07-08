@extends('layouts.app')

@section('content')

<div class="max-w-5xl mx-auto">

    <h1 class="text-3xl font-bold mb-8">
        Gestionar Lead
    </h1>

    <form
        method="POST"
        action="{{ route('leads.update', $lead) }}"
        class="bg-gray-900 border border-gray-800 rounded-3xl p-8">

        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>

                <label class="block text-sm text-gray-400 mb-2">
                    Nombre
                </label>

                <input
                    type="text"
                    value="{{ $lead->nombre }}"
                    readonly
                    class="w-full bg-gray-800 rounded-xl p-3">

            </div>

            <div>

                <label class="block text-sm text-gray-400 mb-2">
                    Teléfono
                </label>

                <input
                    type="text"
                    value="{{ $lead->telefono }}"
                    readonly
                    class="w-full bg-gray-800 rounded-xl p-3">

            </div>

            <div>

                <label class="block text-sm text-gray-400 mb-2">
                    Estado
                </label>

                <select
                    name="estado"
                    class="w-full bg-gray-800 rounded-xl p-3">

                    <option {{ $lead->estado == 'Nuevo' ? 'selected' : '' }}>
                        Nuevo
                    </option>

                    <option {{ $lead->estado == 'Contactado' ? 'selected' : '' }}>
                        Contactado
                    </option>

                    <option {{ $lead->estado == 'Interesado' ? 'selected' : '' }}>
                        Interesado
                    </option>

                    <option {{ $lead->estado == 'Cita' ? 'selected' : '' }}>
                        Cita
                    </option>

                    <option {{ $lead->estado == 'Negociacion' ? 'selected' : '' }}>
                        Negociacion
                    </option>

                    <option {{ $lead->estado == 'Vendido' ? 'selected' : '' }}>
                        Vendido
                    </option>

                    <option {{ $lead->estado == 'Perdido' ? 'selected' : '' }}>
                        Perdido
                    </option>

                </select>

            </div>

            <div>

                <label class="block text-sm text-gray-400 mb-2">
                    Concesionario
                </label>

                <select
                    name="concesionario_id"
                    class="w-full bg-gray-800 rounded-xl p-3">

                    @foreach($concesionarios as $concesionario)

                        <option
                            value="{{ $concesionario->id }}"
                            {{ $lead->concesionario_id == $concesionario->id ? 'selected' : '' }}>

                            {{ $concesionario->nombre }}

                        </option>

                    @endforeach

                </select>

            </div>

        </div>

        <div class="mt-6">

            <label class="block text-sm text-gray-400 mb-2">
                Observación
            </label>

            <textarea
                name="observacion"
                rows="5"
                class="w-full bg-gray-800 rounded-xl p-3">{{ $lead->observacion }}</textarea>

        </div>

        <div class="mt-8 flex gap-4">

            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl">

                Guardar Gestión

            </button>

            <a
                href="{{ route('leads.index') }}"
                class="bg-gray-700 hover:bg-gray-600 px-6 py-3 rounded-xl">

                Cancelar

            </a>

        </div>

    </form>

</div>

@endsection