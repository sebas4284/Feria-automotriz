@extends('layouts.app')

@section('content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl lg:text-3xl font-bold">Estrategia de Ventas</h1>
        <p class="text-gray-400 mt-1 text-sm">Analítica de leads para detectar oportunidades y cuellos de botella</p>
    </div>

    {{-- KPIs principales --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <p class="text-gray-400 text-xs font-medium mb-1">Total leads</p>
            <p class="text-3xl font-bold">{{ $totalLeads }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <p class="text-gray-400 text-xs font-medium mb-1">Tasa de conversión global</p>
            <p class="text-3xl font-bold text-emerald-400">{{ $tasaConversionGlobal }}%</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <p class="text-gray-400 text-xs font-medium mb-1">Leads vencidos (urgentes)</p>
            <p class="text-3xl font-bold text-red-400">{{ $leadsVencidos->count() }}{{ $leadsVencidos->count() >= 20 ? '+' : '' }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <p class="text-gray-400 text-xs font-medium mb-1">Antigüedad promedio (abiertos)</p>
            <p class="text-3xl font-bold">{{ $antiguedad['global'] }} <span class="text-base font-normal text-gray-400">días</span></p>
        </div>
    </div>

    {{-- Embudo de conversión --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h2 class="text-lg font-semibold mb-4">Embudo de conversión</h2>
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
                                labels: @json(array_keys($embudo)),
                                datasets: [{
                                    data: @json(array_values($embudo)),
                                    backgroundColor: ["#3b82f6", "#a855f7", "#f59e0b", "#f59e0b", "#22c55e", "#ef4444"],
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { ticks: { color: "#9ca3af" }, grid: { display: false } },
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
    </div>

    {{-- Conversión por canal / concesionario / asesor --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-base font-semibold mb-4">Conversión por canal</h2>
            @forelse($conversionPorCanal as $fila)
                <div class="flex items-center justify-between py-2 border-b border-gray-800 last:border-0">
                    <div class="min-w-0">
                        <p class="text-sm font-medium truncate">{{ $fila->etiqueta }}</p>
                        <p class="text-xs text-gray-500">{{ $fila->vendidos }} de {{ $fila->total }} leads</p>
                    </div>
                    <span class="text-sm font-bold {{ $fila->tasa >= 20 ? 'text-emerald-400' : 'text-gray-300' }}">{{ $fila->tasa }}%</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Sin datos todavía</p>
            @endforelse
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-base font-semibold mb-4">Conversión por concesionario</h2>
            @forelse($conversionPorConcesionario as $fila)
                <div class="flex items-center justify-between py-2 border-b border-gray-800 last:border-0">
                    <div class="min-w-0">
                        <p class="text-sm font-medium truncate">{{ $fila->etiqueta }}</p>
                        <p class="text-xs text-gray-500">{{ $fila->vendidos }} de {{ $fila->total }} leads</p>
                    </div>
                    <span class="text-sm font-bold {{ $fila->tasa >= 20 ? 'text-emerald-400' : 'text-gray-300' }}">{{ $fila->tasa }}%</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Sin datos todavía</p>
            @endforelse
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-base font-semibold mb-4">Conversión por asesor</h2>
            @forelse($conversionPorAsesor as $fila)
                <div class="flex items-center justify-between py-2 border-b border-gray-800 last:border-0">
                    <div class="min-w-0">
                        <p class="text-sm font-medium truncate">{{ $fila->etiqueta }}</p>
                        <p class="text-xs text-gray-500">{{ $fila->vendidos }} de {{ $fila->total }} leads</p>
                    </div>
                    <span class="text-sm font-bold {{ $fila->tasa >= 20 ? 'text-emerald-400' : 'text-gray-300' }}">{{ $fila->tasa }}%</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Sin datos todavía</p>
            @endforelse
        </div>

    </div>

    {{-- Antigüedad por etapa --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h2 class="text-base font-semibold mb-4">Antigüedad promedio por etapa (leads abiertos)</h2>
        @if($antiguedad['porEtapa']->isEmpty())
            <p class="text-sm text-gray-500">Sin leads abiertos actualmente</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($antiguedad['porEtapa'] as $etapa => $dias)
                    <div class="bg-gray-800/60 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-400">{{ $etapa }}</p>
                        <p class="text-xl font-bold mt-1">{{ $dias }} <span class="text-xs font-normal text-gray-400">días</span></p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Leads vencidos --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-gray-800 flex items-center justify-between">
            <h2 class="text-base font-semibold">Leads vencidos — necesitan atención ya</h2>
            <a href="{{ route('leads.index', ['filtro' => 'vencido']) }}" class="text-xs text-blue-400 hover:text-blue-300 transition">Ver todos →</a>
        </div>
        @if($leadsVencidos->isEmpty())
            <p class="text-sm text-gray-500 p-5">No hay leads vencidos en este momento 🎉</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-800 text-gray-400 uppercase text-xs">
                        <tr>
                            <th class="p-3">Nombre</th>
                            <th class="p-3 hidden sm:table-cell">Concesionario</th>
                            <th class="p-3 hidden md:table-cell">Asesor</th>
                            <th class="p-3">Asignado hace</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leadsVencidos as $lead)
                            <tr class="border-t border-gray-800 hover:bg-gray-800/50 transition cursor-pointer"
                                onclick="window.location='{{ route('leads.show', $lead) }}'">
                                <td class="p-3 font-medium">{{ $lead->full_name ?: 'Sin nombre' }}</td>
                                <td class="p-3 hidden sm:table-cell text-gray-400">{{ $lead->concesionario->nombre ?? 'Sin asignar' }}</td>
                                <td class="p-3 hidden md:table-cell text-gray-400">{{ $lead->asesorComercial->nombre ?? 'Sin asesor' }}</td>
                                <td class="p-3 text-red-400">{{ $lead->assigned_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Patrones de llegada --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-base font-semibold mb-4">Leads por día de la semana</h2>
            <div x-data='{
                    renderChart() {
                        if (typeof Chart === "undefined") { return; }
                        try {
                            new Chart(this.$refs.canvas, {
                                type: "bar",
                                data: {
                                    labels: @json($patronesLlegada["porDiaSemana"]->keys()),
                                    datasets: [{ data: @json($patronesLlegada["porDiaSemana"]->values()), backgroundColor: "#3b82f6" }],
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        x: { ticks: { color: "#9ca3af" }, grid: { display: false } },
                                        y: { beginAtZero: true, ticks: { color: "#9ca3af", precision: 0 }, grid: { color: "#1f2937" } },
                                    },
                                },
                            });
                        } catch (e) {}
                    }
                }' x-init="$nextTick(() => renderChart())" class="relative h-56">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-base font-semibold mb-4">Leads por hora del día</h2>
            <div x-data='{
                    renderChart() {
                        if (typeof Chart === "undefined") { return; }
                        try {
                            new Chart(this.$refs.canvas, {
                                type: "bar",
                                data: {
                                    labels: @json($patronesLlegada["porHora"]->keys()->map(fn ($h) => $h . ":00")),
                                    datasets: [{ data: @json($patronesLlegada["porHora"]->values()), backgroundColor: "#a855f7" }],
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        x: { ticks: { color: "#9ca3af", maxRotation: 0, autoSkip: true, maxTicksLimit: 12 }, grid: { display: false } },
                                        y: { beginAtZero: true, ticks: { color: "#9ca3af", precision: 0 }, grid: { color: "#1f2937" } },
                                    },
                                },
                            });
                        } catch (e) {}
                    }
                }' x-init="$nextTick(() => renderChart())" class="relative h-56">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>

    </div>

</div>

@endsection
