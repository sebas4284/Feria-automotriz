@extends('layouts.app')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

    <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800">
        <h3 class="text-gray-400 text-sm">
            Clientes
        </h3>

        <p class="text-4xl font-bold mt-2 text-blue-500">
            245
        </p>
    </div>

    <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800">
        <h3 class="text-gray-400 text-sm">
            Vehículos
        </h3>

        <p class="text-4xl font-bold mt-2 text-green-500">
            58
        </p>
    </div>

    <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800">
        <h3 class="text-gray-400 text-sm">
            Leads Meta
        </h3>

        <p class="text-4xl font-bold mt-2 text-purple-500">
            189
        </p>
    </div>

    <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800">
        <h3 class="text-gray-400 text-sm">
            Ventas
        </h3>

        <p class="text-4xl font-bold mt-2 text-yellow-500">
            34
        </p>
    </div>

</div>

@endsection