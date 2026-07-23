@extends('layouts.app')

@section('content')

<div class="max-w-5xl mx-auto">

    <h1 class="text-3xl font-bold mb-8">
        Editar Concesionario
    </h1>

    <form
        method="POST"
        action="{{ route('concesionarios.update', $concesionario) }}"
        class="bg-gray-900 border border-gray-800 rounded-3xl p-8">

        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Nombre -->
            <div>
                <label class="block text-sm text-gray-400 mb-2">
                    Nombre del Concesionario
                </label>

                <input
                    type="text"
                    name="nombre"
                    value="{{ $concesionario->nombre }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <!-- NIT -->
            <div>
                <label class="block text-sm text-gray-400 mb-2">
                    NIT
                </label>

                <input
                    type="text"
                    name="nit"
                    value="{{ $concesionario->nit }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Ciudad -->
            <div>
                <label class="block text-sm text-gray-400 mb-2">
                    Ciudad
                </label>

                <input
                    type="text"
                    name="ciudad"
                    value="{{ $concesionario->ciudad }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Teléfono -->
            <div>
                <label class="block text-sm text-gray-400 mb-2">
                    Teléfono
                </label>

                <input
                    type="text"
                    name="telefono"
                    value="{{ $concesionario->telefono }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm text-gray-400 mb-2">
                    Correo Electrónico
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ $concesionario->email }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Responsable -->
            <div>
                <label class="block text-sm text-gray-400 mb-2">
                    Responsable
                </label>

                <input
                    type="text"
                    name="responsable"
                    value="{{ $concesionario->responsable }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Peso -->
            <div>
                <label class="block text-sm text-gray-400 mb-2">
                    Peso de Asignación
                </label>

                <input
                    type="number"
                    name="peso_asignacion"
                    value="{{ $concesionario->peso_asignacion }}"
                    min="1"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Cupo feria -->
            <div>
                <label class="block text-sm text-gray-400 mb-2">
                    Cupo Feria
                </label>

                <input
                    type="number"
                    name="cupo_feria"
                    value="{{ $concesionario->cupo_feria }}"
                    min="0"
                    placeholder="Sin límite"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-sm text-gray-400 mb-2">
                    Estado
                </label>

                <div class="flex items-center h-[50px] gap-3">

                    <input
                        type="checkbox"
                        name="activo"
                        value="1"
                        class="w-5 h-5"
                        {{ $concesionario->activo ? 'checked' : '' }}>

                    <span>
                        Concesionario Activo
                    </span>

                </div>
            </div>

        </div>

        <div class="mt-10 flex gap-4">

            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl font-medium transition">

                Actualizar Concesionario

            </button>

            <a
                href="{{ route('concesionarios.index') }}"
                class="bg-gray-700 hover:bg-gray-600 px-6 py-3 rounded-xl transition">

                Cancelar

            </a>

        </div>

    </form>

</div>

@endsection