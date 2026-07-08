@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- Stats KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">

        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-5 hover:border-blue-500 transition">
            <div class="flex items-center justify-between h-full min-h-[70px]">
                <div class="flex flex-col justify-center">
                    <p class="text-gray-400 text-[11px] font-semibold tracking-wider uppercase leading-tight mb-1">Clientes</p>
                    <h2 class="text-3xl font-bold text-white leading-none">{{ $totalClientes }}</h2>
                </div>
                <div class="p-2.5 bg-blue-500/10 text-blue-500 rounded-xl flex items-center justify-center min-w-[44px] min-h-[44px]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-5 hover:border-green-500 transition">
            <div class="flex items-center justify-between h-full min-h-[70px]">
                <div class="flex flex-col justify-center">
                    <p class="text-gray-400 text-[11px] font-semibold tracking-wider uppercase leading-tight mb-1">Vehículos</p>
                    <h2 class="text-3xl font-bold text-white leading-none">{{ $totalVehiculos }}</h2>
                </div>
                <div class="p-2.5 bg-green-500/10 text-green-500 rounded-xl flex items-center justify-center min-w-[44px] min-h-[44px]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-5 hover:border-purple-500 transition">
            <div class="flex items-center justify-between h-full min-h-[70px]">
                <div class="flex flex-col justify-center">
                    <p class="text-gray-400 text-[11px] font-semibold tracking-wider uppercase leading-tight mb-1">Leads</p>
                    <h2 class="text-3xl font-bold text-white leading-none">{{ $totalLeads }}</h2>
                </div>
                <div class="p-2.5 bg-purple-500/10 text-purple-500 rounded-xl flex items-center justify-center min-w-[44px] min-h-[44px]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-5 hover:border-yellow-500 transition">
            <div class="flex items-center justify-between h-full min-h-[70px]">
                <div class="flex flex-col justify-center">
                    <p class="text-gray-400 text-[11px] font-semibold tracking-wider uppercase leading-tight mb-1">Ventas</p>
                    <h2 class="text-3xl font-bold text-white leading-none">{{ $totalVentas }}</h2>
                </div>
                <div class="p-2.5 bg-yellow-500/10 text-yellow-500 rounded-xl flex items-center justify-center min-w-[44px] min-h-[44px]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="col-span-2 md:col-span-1 bg-gray-900 border border-gray-800 rounded-3xl p-5 hover:border-emerald-500 transition">
            <div class="flex items-center justify-between h-full min-h-[70px]">
                <div class="flex flex-col justify-center">
                    <p class="text-gray-400 text-[11px] font-semibold tracking-wider uppercase leading-tight mb-1">Ingresos del Mes</p>
                    <h2 class="text-2xl font-bold text-emerald-500 leading-none">$ {{ number_format($ingresosMes, 0, ',', '.') }}</h2>
                </div>
                <div class="p-2.5 bg-emerald-500/10 text-emerald-500 rounded-xl flex items-center justify-center min-w-[44px] min-h-[44px]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.854-1.106-2.24 0-3.093 1.147-.884 2.992-.884 4.14 0l.235.18M12 3v18" />
                    </svg>
                </div>
            </div>
        </div>

    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-white">Leads por Día</h2>
                <select class="bg-gray-800 border border-gray-700 text-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                    <option>Últimos 7 días</option>
                    <option>Últimos 30 días</option>
                    <option>Últimos 90 días</option>
                </select>
            </div>
            <div style="height: 160px; position: relative;">
                <canvas id="leadsChart"></canvas>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-white">Estado de Leads</h2>
            </div>
            <div style="height: 220px; position: relative;">
                <canvas id="estadoLeadsChart"></canvas>
            </div>
        </div>

    </div>

    {{-- Pipeline --}}
    <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-white">Pipeline de Leads</h2>
            <span class="text-gray-400 text-sm hidden sm:block">Estado actual de oportunidades</span>
        </div>

        {{-- Móvil: scroll horizontal con chips de resumen --}}
        <div class="flex gap-2 overflow-x-auto pb-3 mb-4 xl:hidden">
            @foreach([
                ['label'=>'Nuevos',     'count'=>$nuevos->count(),       'color'=>'text-blue-400',   'bg'=>'bg-blue-500/10',   'border'=>'border-blue-500/20'],
                ['label'=>'Contactado', 'count'=>$contactados->count(),  'color'=>'text-teal-400',   'bg'=>'bg-teal-500/10',   'border'=>'border-teal-500/20'],
                ['label'=>'Interesado', 'count'=>$interesados->count(),  'color'=>'text-purple-400', 'bg'=>'bg-purple-500/10', 'border'=>'border-purple-500/20'],
                ['label'=>'Cita',       'count'=>$citas->count(),        'color'=>'text-yellow-400', 'bg'=>'bg-yellow-500/10', 'border'=>'border-yellow-500/20'],
                ['label'=>'Negociac.',  'count'=>$negociaciones->count(),'color'=>'text-orange-400', 'bg'=>'bg-orange-500/10', 'border'=>'border-orange-500/20'],
                ['label'=>'Vendido',    'count'=>$vendidos->count(),     'color'=>'text-emerald-400','bg'=>'bg-emerald-500/10','border'=>'border-emerald-500/20'],
            ] as $s)
            <div class="flex-shrink-0 {{ $s['bg'] }} border {{ $s['border'] }} rounded-2xl px-4 py-3 text-center min-w-[90px]">
                <p class="text-xl font-bold {{ $s['color'] }}">{{ $s['count'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $s['label'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Desktop: kanban columns --}}
        <div class="hidden xl:grid grid-cols-6 gap-4">

            <div class="bg-gray-800/50 rounded-2xl p-4 border border-gray-800">
                <h3 class="font-semibold text-blue-400 mb-4 flex justify-between items-center">
                    <span>Nuevos</span>
                    <span class="bg-blue-500/10 text-blue-400 text-xs px-2 py-0.5 rounded-full">{{ $nuevos->count() }}</span>
                </h3>
                @foreach($nuevos as $lead)
                    <div class="bg-gray-900 rounded-xl p-3 mb-3 border border-gray-800 hover:border-gray-700 transition">
                        <p class="font-semibold text-gray-200 text-sm">{{ $lead->nombre }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $lead->vehiculo_interes }}</p>
                        <p class="text-xs text-blue-400 mt-2 font-medium">{{ $lead->concesionario?->nombre }}</p>
                    </div>
                @endforeach
            </div>

            <div class="bg-gray-800/50 rounded-2xl p-4 border border-gray-800">
                <h3 class="font-semibold text-green-400 mb-4 flex justify-between items-center">
                    <span>Contactados</span>
                    <span class="bg-green-500/10 text-green-400 text-xs px-2 py-0.5 rounded-full">{{ $contactados->count() }}</span>
                </h3>
                @foreach($contactados as $lead)
                    <div class="bg-gray-900 rounded-xl p-3 mb-3 border border-gray-800 hover:border-gray-700 transition">
                        <p class="font-semibold text-gray-200 text-sm">{{ $lead->nombre }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $lead->vehiculo_interes }}</p>
                        <p class="text-xs text-green-400 mt-2 font-medium">{{ $lead->concesionario?->nombre }}</p>
                    </div>
                @endforeach
            </div>

            <div class="bg-gray-800/50 rounded-2xl p-4 border border-gray-800">
                <h3 class="font-semibold text-purple-400 mb-4 flex justify-between items-center">
                    <span>Interesados</span>
                    <span class="bg-purple-500/10 text-purple-400 text-xs px-2 py-0.5 rounded-full">{{ $interesados->count() }}</span>
                </h3>
                @foreach($interesados as $lead)
                    <div class="bg-gray-900 rounded-xl p-3 mb-3 border border-gray-800 hover:border-gray-700 transition">
                        <p class="font-semibold text-gray-200 text-sm">{{ $lead->nombre }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $lead->vehiculo_interes }}</p>
                        <p class="text-xs text-purple-400 mt-2 font-medium">{{ $lead->concesionario?->nombre }}</p>
                    </div>
                @endforeach
            </div>

            <div class="bg-gray-800/50 rounded-2xl p-4 border border-gray-800">
                <h3 class="font-semibold text-yellow-400 mb-4 flex justify-between items-center">
                    <span>Citas</span>
                    <span class="bg-yellow-500/10 text-yellow-400 text-xs px-2 py-0.5 rounded-full">{{ $citas->count() }}</span>
                </h3>
                @foreach($citas as $lead)
                    <div class="bg-gray-900 rounded-xl p-3 mb-3 border border-gray-800 hover:border-gray-700 transition">
                        <p class="font-semibold text-gray-200 text-sm">{{ $lead->nombre }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $lead->vehiculo_interes }}</p>
                        <p class="text-xs text-yellow-400 mt-2 font-medium">{{ $lead->concesionario?->nombre }}</p>
                    </div>
                @endforeach
            </div>

            <div class="bg-gray-800/50 rounded-2xl p-4 border border-gray-800">
                <h3 class="font-semibold text-orange-400 mb-4 flex justify-between items-center">
                    <span>Negociación</span>
                    <span class="bg-orange-500/10 text-orange-400 text-xs px-2 py-0.5 rounded-full">{{ $negociaciones->count() }}</span>
                </h3>
                @foreach($negociaciones as $lead)
                    <div class="bg-gray-900 rounded-xl p-3 mb-3 border border-gray-800 hover:border-gray-700 transition">
                        <p class="font-semibold text-gray-200 text-sm">{{ $lead->nombre }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $lead->vehiculo_interes }}</p>
                        <p class="text-xs text-orange-400 mt-2 font-medium">{{ $lead->concesionario?->nombre }}</p>
                    </div>
                @endforeach
            </div>

            <div class="bg-gray-800/50 rounded-2xl p-4 border border-gray-800">
                <h3 class="font-semibold text-emerald-400 mb-4 flex justify-between items-center">
                    <span>Vendidos</span>
                    <span class="bg-emerald-500/10 text-emerald-400 text-xs px-2 py-0.5 rounded-full">{{ $vendidos->count() }}</span>
                </h3>
                @foreach($vendidos as $lead)
                    <div class="bg-gray-900 rounded-xl p-3 mb-3 border border-gray-800 hover:border-gray-700 transition">
                        <p class="font-semibold text-gray-200 text-sm">{{ $lead->nombre }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $lead->vehiculo_interes }}</p>
                        <p class="text-xs text-emerald-400 mt-2 font-medium">{{ $lead->concesionario?->nombre }}</p>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const leadsCtx = document.getElementById('leadsChart');
        if (leadsCtx) {
            new Chart(leadsCtx, {
                type: 'line',
                data: {
                    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                    datasets: [{
                        label: 'Leads',
                        data: [4, 7, 5, 9, 12, 8, 11],
                        borderWidth: 3,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#9ca3af' } },
                        y: { grid: { color: '#1f2937' }, ticks: { color: '#9ca3af' } }
                    }
                }
            });
        }

        const estadoCtx = document.getElementById('estadoLeadsChart');
        if (estadoCtx) {
            new Chart(estadoCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Nuevo', 'Contactado', 'Interesado', 'Cita', 'Negociación', 'Vendido'],
                    datasets: [{
                        data: [
                            {{ $nuevos->count() }},
                            {{ $contactados->count() }},
                            {{ $interesados->count() }},
                            {{ $citas->count() }},
                            {{ $negociaciones->count() }},
                            {{ $vendidos->count() }}
                        ],
                        backgroundColor: ['#3b82f6', '#10b981', '#a855f7', '#f59e0b', '#f97316', '#10b981'],
                        borderColor: '#111827',
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { padding: 15, font: { size: 12 }, color: '#9ca3af' }
                        }
                    }
                }
            });
        }
    });
</script>

@endsection
