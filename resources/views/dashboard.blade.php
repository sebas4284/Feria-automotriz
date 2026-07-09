@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- Stats KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-2 xl:grid-cols-4 gap-4">

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

</div>

@endsection
