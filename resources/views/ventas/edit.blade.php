@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold">Editar Venta</h1>
            <p class="text-gray-400">Venta #{{ $venta->id }}</p>
        </div>
        <a href="{{ route('ventas.show', $venta) }}"
            class="bg-gray-800 hover:bg-gray-700 px-5 py-3 rounded-xl">
            Volver
        </a>
    </div>

    @include('ventas._form', ['venta' => $venta])

</div>

@endsection
