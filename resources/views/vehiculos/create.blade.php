@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

    <h1 class="text-3xl font-bold mb-8">
        Nuevo Vehículo
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
        action="{{ route('vehiculos.store') }}"
        method="POST"
        enctype="multipart/form-data"
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