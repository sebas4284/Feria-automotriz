@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="flex justify-between items-center mb-8">

        <div>

            <h1 class="text-3xl font-bold">
                Nueva Venta
            </h1>

            <p class="text-gray-400">
                Registrar una venta realizada
            </p>

        </div>

        <a href="{{ route('ventas.index') }}"
            class="bg-gray-800 hover:bg-gray-700 px-5 py-3 rounded-xl">

            Volver

        </a>

    </div>

    <form
        action="{{ route('ventas.store') }}"
        method="POST"
        class="bg-gray-900 border border-gray-800 rounded-3xl p-8">

        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>

                <label class="block mb-2 text-sm text-gray-400">
                    Cliente
                </label>

                <select
                    name="cliente_id"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3"
                    required>

                    <option value="">
                        Seleccione...
                    </option>

                    @foreach($clientes as $cliente)

                        <option value="{{ $cliente->id }}">
                            {{ $cliente->nombre }}
                        </option>

                    @endforeach

                </select>

            </div>

            <div>

                <label class="block mb-2 text-sm text-gray-400">
                    Vehículo
                </label>

                <select
                    name="vehiculo_id"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3"
                    required>

                    <option value="">
                        Seleccione...
                    </option>

                    @foreach($vehiculos as $vehiculo)

                        <option value="{{ $vehiculo->id }}">
                            {{ $vehiculo->marca }}
                            {{ $vehiculo->modelo }}
                            (Stock: {{ $vehiculo->stock }})
                        </option>

                    @endforeach

                </select>

            </div>

            <div>

                <label class="block mb-2 text-sm text-gray-400">
                    Valor de Venta
                </label>

                <input
                    type="number"
                    name="valor"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3"
                    required>

            </div>

            <div>

                <label class="block mb-2 text-sm text-gray-400">
                    Fecha
                </label>

                <input
                    type="date"
                    name="fecha_venta"
                    value="{{ date('Y-m-d') }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3"
                    required>

            </div>

        </div>

        <div class="mt-6">

            <label class="block mb-2 text-sm text-gray-400">
                Forma de Pago
            </label>

            <input
                type="text"
                name="forma_pago"
                placeholder="Contado, Crédito, Leasing..."
                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

        </div>

        <div class="mt-6">

            <label class="block mb-2 text-sm text-gray-400">
                Observaciones
            </label>

            <textarea
                name="observaciones"
                rows="4"
                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3"></textarea>

        </div>

        <div class="mt-8">

            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl font-medium">

                Registrar Venta

            </button>

        </div>

    </form>

</div>

@endsection