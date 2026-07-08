@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

    <h1 class="text-3xl font-bold mb-8">
        Nuevo Vehículo
    </h1>

    <form
        action="{{ route('vehiculos.store') }}"
        method="POST"
        class="bg-gray-900 border border-gray-800 rounded-3xl p-8">

        @csrf

        @include('vehiculos._form')

        <div class="mt-8">

            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl">

                Guardar Vehículo

            </button>

        </div>

    </form>

</div>

@endsection