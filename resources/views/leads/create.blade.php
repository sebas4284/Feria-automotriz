@extends('layouts.app')

@section('content')

<div class="max-w-5xl mx-auto">

    <h1 class="text-3xl font-bold mb-8">
        Nuevo Lead
    </h1>

    <form
        method="POST"
        action="{{ route('leads.store') }}"
        class="bg-gray-900 border border-gray-800 rounded-3xl p-8">

        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>

                <label class="block text-sm text-gray-400 mb-2">
                    Nombre
                </label>

                <input
                    type="text"
                    name="nombre"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3">

            </div>

            <div>

                <label class="block text-sm text-gray-400 mb-2">
                    Teléfono
                </label>

                <input
                    type="text"
                    name="telefono"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3">

            </div>

            <div>

                <label class="block text-sm text-gray-400 mb-2">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3">

            </div>

            <div>

                <label class="block text-sm text-gray-400 mb-2">
                    Ciudad
                </label>

                <input
                    type="text"
                    name="ciudad"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3">

            </div>

            <div class="md:col-span-2">

                <label class="block text-sm text-gray-400 mb-2">
                    Vehículo de Interés
                </label>

                <input
                    type="text"
                    name="vehiculo_interes"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3">

            </div>

        </div>

        <button
            type="submit"
            class="mt-8 bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl">

            Guardar Lead

        </button>

    </form>

</div>

@endsection