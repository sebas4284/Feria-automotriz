@extends('layouts.app')

@section('content')

    <div class="max-w-4xl mx-auto">

        <h1 class="text-3xl font-bold mb-6">
            Nuevo Concesionario
        </h1>

        <form method="POST" action="{{ route('concesionarios.store') }}"
            class="bg-gray-900 border border-gray-800 rounded-3xl p-8">

            @csrf

            <div class="grid grid-cols-2 gap-6">

                <input name="nombre" placeholder="Nombre" class="bg-gray-800 rounded-xl p-3">

                <input name="nit" placeholder="NIT" class="bg-gray-800 rounded-xl p-3">

                <input name="ciudad" placeholder="Ciudad" class="bg-gray-800 rounded-xl p-3">

                <input name="telefono" placeholder="Teléfono" class="bg-gray-800 rounded-xl p-3">

                <input name="email" placeholder="Email" class="bg-gray-800 rounded-xl p-3">

                <input name="responsable" placeholder="Responsable" class="bg-gray-800 rounded-xl p-3">

                <input type="number" name="peso_asignacion" value="1" min="1" placeholder="Peso de asignación"
                    class="bg-gray-800 rounded-xl p-3">

                <input type="number" name="cupo_feria" min="0" placeholder="Cupo feria (opcional)"
                    class="bg-gray-800 rounded-xl p-3">

            </div>

            <button class="mt-6 bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl">

                Guardar

            </button>

        </form>

    </div>

@endsection