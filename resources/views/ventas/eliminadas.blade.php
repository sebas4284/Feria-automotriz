@extends('layouts.app')

@section('content')

<div class="max-w-6xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('ventas.index') }}" class="text-sm text-blue-400 hover:underline">&larr; Volver a Ventas</a>
        <h1 class="text-2xl lg:text-3xl font-bold mt-2">Ventas eliminadas</h1>
        <p class="text-gray-400 text-sm mt-0.5">Historial de ventas borradas, con el motivo registrado</p>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden">
        <div class="p-5 border-b border-gray-800">
            <h2 class="text-lg font-semibold">
                Registro
                <span class="text-sm font-normal text-gray-400 ml-2">({{ $eliminadas->count() }})</span>
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-800 text-gray-400 text-sm uppercase">
                    <tr>
                        <th class="p-4">Venta</th>
                        <th class="p-4">Vehículo</th>
                        <th class="p-4">Comprador</th>
                        <th class="p-4">Concesionario</th>
                        <th class="p-4">Valor</th>
                        <th class="p-4">Motivo</th>
                        <th class="p-4">Eliminado por</th>
                        <th class="p-4">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($eliminadas as $item)
                        <tr>
                            <td class="p-4">#{{ $item->venta_id }}</td>
                            <td class="p-4">
                                <span class="font-mono">{{ $item->vehiculo_placa ?: '—' }}</span>
                                <span class="text-gray-400 text-sm block">{{ $item->vehiculo_marca }} {{ $item->vehiculo_modelo }}</span>
                            </td>
                            <td class="p-4">{{ $item->comprador_nombre ?: '—' }}</td>
                            <td class="p-4">{{ $item->concesionario_vende_nombre ?: '—' }}</td>
                            <td class="p-4 text-emerald-400 font-bold">$ {{ number_format($item->valor, 0, ',', '.') }}</td>
                            <td class="p-4 max-w-xs">{{ $item->motivo }}</td>
                            <td class="p-4">{{ $item->eliminado_por_nombre ?: '—' }}</td>
                            <td class="p-4 text-gray-400">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-500">
                                No hay ventas eliminadas registradas todavía
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
