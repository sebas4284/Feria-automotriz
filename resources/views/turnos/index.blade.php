@extends('layouts.app')

@section('content')

@php
    $posiciones = $enFila->values()->mapWithKeys(fn($c, $i) => [$c->id => $i + 1]);
@endphp

{{-- ===================== VISTA MÓVIL ===================== --}}
<div class="lg:hidden space-y-5">

    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Turnos</h1>
            <p class="text-gray-400 text-sm mt-0.5">Llegada de concesionarios — hoy {{ now()->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('turnos.pantalla') }}" target="_blank"
            class="shrink-0 bg-gray-800 hover:bg-gray-700 text-xs px-3 py-2 rounded-xl transition">
            Pantalla grande
        </a>
    </div>

    @if($rondaActual > 0)
        <div class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3 flex items-center justify-between">
            <span class="text-sm text-gray-400">Ronda actual</span>
            <span class="text-xl font-bold text-blue-400">#{{ $rondaActual }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-2xl px-4 py-3 text-green-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/30 rounded-2xl px-4 py-3 text-red-400 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if(!$siguiente)
        <div class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-3 text-sm text-gray-500">
            Ningún concesionario ha marcado llegada hoy. Los clientes sin cita quedarán sin asignar hasta que alguien llegue.
        </div>
    @endif

    <div class="space-y-2">
        @foreach($concesionariosOrdenados as $c)
            @php $turno = $turnosHoy->get($c->id); @endphp
            <div class="bg-gray-900 border {{ $siguiente && $siguiente->id === $c->id ? 'border-blue-500' : 'border-gray-800' }} rounded-2xl px-4 py-3.5">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-sm truncate">{{ $c->nombre }}</p>
                        @if($turno)
                            <p class="text-xs text-gray-500 mt-0.5">
                                Turno #{{ $posiciones[$c->id] ?? '—' }} · llegó {{ $turno->llegada_at->format('H:i') }}
                            </p>
                        @else
                            <p class="text-xs text-gray-600 mt-0.5">No ha llegado</p>
                        @endif
                    </div>

                    @if(auth()->user()->isAdmin())
                        @if($turno)
                            <div class="flex items-center gap-2 shrink-0">
                                <form action="{{ route('turnos.saltar', $c) }}" method="POST">
                                    @csrf
                                    <button type="submit" title="Saltar turno (pasa al final)"
                                        class="px-3 py-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 text-xs font-bold transition">
                                        ✕
                                    </button>
                                </form>
                                <form action="{{ route('turnos.check-out', $c) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-medium transition">
                                        Quitar
                                    </button>
                                </form>
                            </div>
                        @else
                            <form action="{{ route('turnos.check-in', $c) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-xs font-medium transition shrink-0">
                                    Marcar llegada
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if($pendientes->isNotEmpty())
        <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-4">
            <p class="text-amber-400 text-sm font-medium mb-1">{{ $pendientes->count() }} cliente(s) pendiente(s) por asignar</p>
            <p class="text-xs text-gray-400">En un monitor grande (mouse) puedes arrastrarlos al concesionario. Desde el celular, pídele a alguien en escritorio que los asigne en {{ route('turnos.index') }}.</p>
        </div>
    @endif

    <div>
        <h2 class="text-base font-semibold text-gray-200 mb-3">Clientes sin cita de hoy</h2>
        <div class="space-y-2">
            @forelse($clientesHoy as $cliente)
                <div class="bg-gray-900 border {{ $cliente->concesionario_id ? 'border-gray-800' : 'border-dashed border-amber-500/50' }} rounded-2xl px-4 py-3.5 flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-medium text-sm truncate">{{ $cliente->nombre }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $cliente->created_at->format('H:i') }}</p>
                    </div>
                    <span class="text-xs {{ $cliente->concesionario ? 'bg-green-500/20 text-green-400' : 'bg-amber-500/20 text-amber-400' }} px-2 py-0.5 rounded-full shrink-0">
                        {{ $cliente->concesionario->nombre ?? 'Pendiente' }}
                    </span>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500 text-sm">
                    Todavía no ha llegado ningún cliente sin cita hoy
                </div>
            @endforelse
        </div>
    </div>

</div>

{{-- ===================== VISTA DESKTOP ===================== --}}
<div class="hidden lg:block">

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold">Turnos</h1>
            <p class="text-gray-400">Llegada de concesionarios — hoy {{ now()->format('d/m/Y') }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if($rondaActual > 0)
                <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 flex items-center gap-2">
                    <span class="text-sm text-gray-400">Ronda actual</span>
                    <span class="text-lg font-bold text-blue-400">#{{ $rondaActual }}</span>
                </div>
            @endif
            <a href="{{ route('turnos.pantalla') }}" target="_blank"
                class="bg-gray-800 hover:bg-gray-700 px-4 py-2.5 rounded-xl text-sm transition">
                Abrir pantalla grande
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/50 rounded-xl p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-500/10 border border-red-500/50 rounded-xl p-4 text-red-400">
            {{ session('error') }}
        </div>
    @endif

    @if(!$siguiente)
        <div class="mb-6 bg-gray-900 border border-gray-800 rounded-xl p-4 text-gray-500">
            Ningún concesionario ha marcado llegada hoy. Los clientes sin cita quedarán sin asignar hasta que alguien llegue.
        </div>
    @endif

    @if($pendientes->isNotEmpty())
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-3 text-amber-400">
                Pendientes por asignar — arrastra al concesionario
            </h2>
            <div class="flex flex-wrap gap-3">
                @foreach($pendientes as $cliente)
                    <div draggable="true"
                        ondragstart="event.dataTransfer.setData('text/plain', '{{ $cliente->id }}')"
                        class="cursor-grab active:cursor-grabbing bg-amber-500/10 border border-dashed border-amber-500/50 rounded-2xl px-4 py-3 select-none">
                        <p class="font-medium text-sm">{{ $cliente->nombre }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $cliente->created_at->format('H:i') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-800">
                <tr>
                    <th class="p-4 text-left">Concesionario</th>
                    <th class="p-4 text-left">Estado</th>
                    <th class="p-4 text-left">Turno</th>
                    <th class="p-4 text-left">Llegada</th>
                    <th class="p-4 text-left">Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($concesionariosOrdenados as $c)
                    @php $turno = $turnosHoy->get($c->id); @endphp
                    <tr class="border-t border-gray-800 {{ $siguiente && $siguiente->id === $c->id ? 'bg-blue-500/5' : '' }} hover:bg-gray-800/40 transition"
                        @if($turno)
                            ondragover="event.preventDefault()"
                            ondrop="asignarClienteArrastrado(event, {{ $c->id }})"
                        @endif
                    >
                        <td class="p-4 font-medium">
                            {{ $c->nombre }}
                            @if($siguiente && $siguiente->id === $c->id)
                                <span class="ml-2 text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded-full">Sugerido</span>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($turno)
                                <span class="text-xs bg-green-500/20 text-green-400 px-3 py-1 rounded-full">En fila</span>
                            @else
                                <span class="text-xs bg-gray-700 text-gray-400 px-3 py-1 rounded-full">No ha llegado</span>
                            @endif
                        </td>
                        <td class="p-4">{{ $turno ? '#' . ($posiciones[$c->id] ?? '—') : '—' }}</td>
                        <td class="p-4">{{ $turno?->llegada_at->format('H:i') ?? '—' }}</td>
                        <td class="p-4">
                            @if(auth()->user()->isAdmin())
                                @if($turno)
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('turnos.saltar', $c) }}" method="POST">
                                            @csrf
                                            <button type="submit" title="Saltar turno (pasa al final)"
                                                class="px-3 py-1.5 rounded-lg bg-amber-500/20 hover:bg-amber-500/40 text-amber-400 text-xs font-bold transition">
                                                ✕
                                            </button>
                                        </form>
                                        <form action="{{ route('turnos.check-out', $c) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-400 text-xs font-medium transition">
                                                Quitar de la fila
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <form action="{{ route('turnos.check-in', $c) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-xs font-medium transition">
                                            Marcar llegada
                                        </button>
                                    </form>
                                @endif
                            @else
                                <span class="text-gray-600">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        <h2 class="text-xl font-semibold mb-4">Clientes sin cita de hoy</h2>
        <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="p-4 text-left">Cliente</th>
                        <th class="p-4 text-left">Hora</th>
                        <th class="p-4 text-left">Concesionario asignado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientesHoy as $cliente)
                        <tr class="border-t border-gray-800 hover:bg-gray-800/40 transition">
                            <td class="p-4 font-medium">{{ $cliente->nombre }}</td>
                            <td class="p-4">{{ $cliente->created_at->format('H:i') }}</td>
                            <td class="p-4">
                                <span class="text-xs {{ $cliente->concesionario ? 'bg-green-500/20 text-green-400' : 'bg-amber-500/20 text-amber-400' }} px-3 py-1 rounded-full">
                                    {{ $cliente->concesionario->nombre ?? 'Pendiente' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-8 text-center text-gray-500">Todavía no ha llegado ningún cliente sin cita hoy</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    async function asignarClienteArrastrado(event, concesionarioId) {
        event.preventDefault();
        const clienteId = event.dataTransfer.getData('text/plain');
        if (!clienteId) return;

        const response = await fetch("{{ route('turnos.asignar-cliente') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ cliente_id: clienteId, concesionario_id: concesionarioId }),
        });

        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            alert(data.message || 'No se pudo asignar el cliente.');
            return;
        }

        window.location.reload();
    }

    setTimeout(() => window.location.reload(), 15000);
</script>

@endsection
