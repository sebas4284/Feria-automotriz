@extends('layouts.app')

@section('content')

<div>

    <div class="mb-6">
        <a href="{{ route('ventas.index') }}" class="text-sm text-blue-400 hover:underline">&larr; Volver a Ventas</a>
        <h1 class="text-2xl lg:text-3xl font-bold mt-2">{{ $esGlobal ? 'Análisis de Ventas' : 'Mi Análisis de Ventas' }}</h1>
        <p class="text-gray-400 mt-1 text-sm">{{ $esGlobal ? 'Resumen general del evento' : 'Resumen de tus ventas' }}</p>
    </div>

    <form method="GET" class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-6 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-400 mb-1">Desde</label>
            <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Hasta</label>
            <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500">
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-5 py-1.5 rounded-lg text-sm font-medium transition">Filtrar</button>
        @if($fechaDesde || $fechaHasta)
            <a href="{{ url()->current() }}" class="text-xs text-gray-400 hover:text-white transition px-1 py-1.5">✕ Limpiar</a>
        @endif
    </form>

    {{-- KPIs generales --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 lg:p-5">
            <p class="text-gray-400 text-xs lg:text-sm font-medium mb-1">Total de ventas</p>
            <p class="text-2xl lg:text-3xl font-bold">{{ $totalVentas }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 lg:p-5">
            <p class="text-gray-400 text-xs lg:text-sm font-medium mb-1">Valor total vendido</p>
            <p class="text-xl lg:text-2xl font-bold text-emerald-400">$ {{ number_format($valorTotal, 0, ',', '.') }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 lg:p-5">
            <p class="text-gray-400 text-xs lg:text-sm font-medium mb-1">Valor promedio por venta</p>
            <p class="text-xl lg:text-2xl font-bold text-blue-400">$ {{ number_format($promedioVenta, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Ventas y valor por día --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-lg font-semibold mb-4">Ventas por día</h2>
            @if($ventasPorDia->isEmpty())
                <p class="text-sm text-gray-500 text-center py-8">Sin datos todavía</p>
            @else
                <div x-data='{
                        renderChart() {
                            if (typeof Chart === "undefined") {
                                this.$refs.canvas.replaceWith(document.createTextNode("Chart.js no cargó (window.Chart es undefined)"));
                                return;
                            }
                            try {
                                new Chart(this.$refs.canvas, {
                                    type: "bar",
                                    data: {
                                        labels: @json($ventasPorDia->pluck('fecha_venta')->map(fn ($f) => $f->format('d M'))),
                                        datasets: [{
                                            data: @json($ventasPorDia->pluck('total_ventas')),
                                            backgroundColor: "#3b82f6",
                                            borderRadius: 4,
                                        }],
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { display: false } },
                                        scales: {
                                            x: { ticks: { color: "#9ca3af" }, grid: { color: "#1f2937" } },
                                            y: { beginAtZero: true, ticks: { color: "#9ca3af", precision: 0 }, grid: { color: "#1f2937" } },
                                        },
                                    },
                                });
                            } catch (e) {
                                this.$refs.canvas.replaceWith(document.createTextNode("Error al cargar el gráfico: " + e.message));
                            }
                        }
                    }' x-init="$nextTick(() => renderChart())" class="relative h-64">
                    <canvas x-ref="canvas"></canvas>
                </div>
            @endif
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-lg font-semibold mb-4">Valor vendido por día</h2>
            @if($ventasPorDia->isEmpty())
                <p class="text-sm text-gray-500 text-center py-8">Sin datos todavía</p>
            @else
                <div x-data='{
                        renderChart() {
                            if (typeof Chart === "undefined") {
                                this.$refs.canvas.replaceWith(document.createTextNode("Chart.js no cargó (window.Chart es undefined)"));
                                return;
                            }
                            try {
                                new Chart(this.$refs.canvas, {
                                    type: "line",
                                    data: {
                                        labels: @json($ventasPorDia->pluck('fecha_venta')->map(fn ($f) => $f->format('d M'))),
                                        datasets: [{
                                            data: @json($ventasPorDia->pluck('total_valor')),
                                            borderColor: "#22c55e",
                                            backgroundColor: "rgba(34,197,94,0.15)",
                                            fill: true,
                                            tension: 0.3,
                                            pointRadius: 2,
                                        }],
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { display: false } },
                                        scales: {
                                            x: { ticks: { color: "#9ca3af" }, grid: { color: "#1f2937" } },
                                            y: { beginAtZero: true, ticks: { color: "#9ca3af" }, grid: { color: "#1f2937" } },
                                        },
                                    },
                                });
                            } catch (e) {
                                this.$refs.canvas.replaceWith(document.createTextNode("Error al cargar el gráfico: " + e.message));
                            }
                        }
                    }' x-init="$nextTick(() => renderChart())" class="relative h-64">
                    <canvas x-ref="canvas"></canvas>
                </div>
            @endif
        </div>
    </div>

    {{-- Forma de pago --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-lg font-semibold mb-4">Forma de pago</h2>
            @if($porFormaPago->isEmpty())
                <p class="text-sm text-gray-500 text-center py-8">Sin datos todavía</p>
            @else
                <div x-data='{
                        renderChart() {
                            if (typeof Chart === "undefined") {
                                this.$refs.canvas.replaceWith(document.createTextNode("Chart.js no cargó (window.Chart es undefined)"));
                                return;
                            }
                            try {
                                new Chart(this.$refs.canvas, {
                                    type: "doughnut",
                                    data: {
                                        labels: @json($porFormaPago->pluck('forma_pago')),
                                        datasets: [{
                                            data: @json($porFormaPago->pluck('total_ventas')),
                                            backgroundColor: ["#3b82f6", "#ec4899", "#22c55e", "#f59e0b"],
                                            borderColor: "#111827",
                                        }],
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { position: "bottom", labels: { color: "#d1d5db", boxWidth: 12 } } },
                                    },
                                });
                            } catch (e) {
                                this.$refs.canvas.replaceWith(document.createTextNode("Error al cargar el gráfico: " + e.message));
                            }
                        }
                    }' x-init="$nextTick(() => renderChart())" class="relative h-64">
                    <canvas x-ref="canvas"></canvas>
                </div>
            @endif
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-gray-800">
                <h2 class="text-lg font-semibold">Detalle por forma de pago</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-800 text-gray-400 text-sm uppercase">
                        <tr>
                            <th class="p-4">Forma de pago</th>
                            <th class="p-4">Ventas</th>
                            <th class="p-4">Valor</th>
                            <th class="p-4">% del total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @forelse($porFormaPago as $item)
                            <tr>
                                <td class="p-4">{{ $item->forma_pago }}</td>
                                <td class="p-4">{{ $item->total_ventas }}</td>
                                <td class="p-4 text-emerald-400">$ {{ number_format($item->total_valor, 0, ',', '.') }}</td>
                                <td class="p-4 text-gray-400">{{ $valorTotal > 0 ? number_format($item->total_valor / $valorTotal * 100, 1) : 0 }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-500">Sin datos todavía</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Créditos por banco --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden mb-8">
        <div class="p-5 border-b border-gray-800">
            <h2 class="text-lg font-semibold">Créditos por banco</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-800 text-gray-400 text-sm uppercase">
                    <tr>
                        <th class="p-4">Banco</th>
                        <th class="p-4">Ventas</th>
                        <th class="p-4">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($porBanco as $item)
                        <tr>
                            <td class="p-4">{{ $item->banco }}</td>
                            <td class="p-4">{{ $item->total_ventas }}</td>
                            <td class="p-4 text-emerald-400">$ {{ number_format($item->total_valor, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-8 text-center text-gray-500">Sin créditos registrados todavía</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($esGlobal)
    {{-- Ranking de concesionarios --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden mb-8">
        <div class="p-5 border-b border-gray-800">
            <h2 class="text-lg font-semibold">Ranking de concesionarios</h2>
            <p class="text-xs text-gray-500 mt-1">"Vendidas" = autos que vendió este concesionario. "De su inventario" = autos suyos que vendió otro concesionario (venta cruzada).</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-800 text-gray-400 text-sm uppercase">
                    <tr>
                        <th class="p-4">Concesionario</th>
                        <th class="p-4">Vendidas (como vendedor)</th>
                        <th class="p-4">De su inventario (vendidas por otro)</th>
                        <th class="p-4">Total autos</th>
                        <th class="p-4">Total $</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($rankingConcesionarios as $item)
                        <tr>
                            <td class="p-4 font-medium">{{ $item['nombre'] }}</td>
                            <td class="p-4">{{ $item['vendidas_ventas'] }} <span class="text-gray-500">(${{ number_format($item['vendidas_valor'], 0, ',', '.') }})</span></td>
                            <td class="p-4">{{ $item['cruzadas_ventas'] }} <span class="text-gray-500">(${{ number_format($item['cruzadas_valor'], 0, ',', '.') }})</span></td>
                            <td class="p-4 font-semibold">{{ $item['total_ventas'] }}</td>
                            <td class="p-4 text-emerald-400 font-semibold">$ {{ number_format($item['total_valor'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">Sin datos todavía</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Ranking de asesores --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden mb-8">
        <div class="p-5 border-b border-gray-800">
            <h2 class="text-lg font-semibold">{{ $esGlobal ? 'Ranking de asesores comerciales' : 'Tus asesores comerciales' }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-800 text-gray-400 text-sm uppercase">
                    <tr>
                        <th class="p-4">Asesor</th>
                        <th class="p-4">Concesionario</th>
                        <th class="p-4">Ventas</th>
                        <th class="p-4">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($porAsesor as $item)
                        <tr>
                            <td class="p-4">{{ $item->asesorComercial->nombre ?? '—' }}</td>
                            <td class="p-4">{{ $item->asesorComercial->concesionario->nombre ?? '—' }}</td>
                            <td class="p-4">{{ $item->total_ventas }}</td>
                            <td class="p-4 text-emerald-400">$ {{ number_format($item->total_valor, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-500">Sin datos todavía</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Informe de ventas cruzadas --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-gray-800">
            <h2 class="text-lg font-semibold">Ventas cruzadas</h2>
            <p class="text-xs text-gray-500 mt-1">Ventas donde el concesionario que vendió el auto no es el dueño del vehículo.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-800 text-gray-400 text-sm uppercase">
                    <tr>
                        <th class="p-4">Placa</th>
                        <th class="p-4">Dueño</th>
                        <th class="p-4">Vendedor</th>
                        <th class="p-4">Comprador</th>
                        <th class="p-4">Valor</th>
                        <th class="p-4">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($ventasCruzadas as $venta)
                        <tr>
                            <td class="p-4 font-mono">{{ $venta->vehiculo->placa }}</td>
                            <td class="p-4">{{ $venta->vehiculo->concesionario->nombre ?? '—' }}</td>
                            <td class="p-4">{{ $venta->concesionarioVende->nombre ?? '—' }}</td>
                            <td class="p-4">{{ $venta->comprador->nombre ?? '—' }}</td>
                            <td class="p-4 text-emerald-400">$ {{ number_format($venta->valor, 0, ',', '.') }}</td>
                            <td class="p-4 text-gray-400">{{ $venta->fecha_venta?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">No hay ventas cruzadas registradas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
