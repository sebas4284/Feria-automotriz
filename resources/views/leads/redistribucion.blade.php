@extends('layouts.app')

@section('content')

<div>

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold">Redistribución de leads</h1>
            <p class="text-gray-400 mt-1 text-sm">Leads vencidos y sin asesor repartidos entre concesionarios</p>
        </div>
        <a href="{{ route('leads.index') }}" class="px-3 py-1.5 rounded-xl text-sm bg-gray-800 text-gray-300 hover:text-white transition">
            &larr; Volver a leads
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/50 rounded-xl p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if(auth()->user()->isAdmin())
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-gray-400 text-sm">Leads vencidos y sin asesor listos para repartir</p>
                <p class="text-3xl font-bold text-red-400">{{ $candidatosCount }}</p>
            </div>
            <form method="POST" action="{{ route('leads.redistribucion.ejecutar') }}"
                onsubmit="return confirm('¿Repartir estos leads entre los concesionarios configurados?')">
                @csrf
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 rounded-xl px-4 py-2 text-sm font-medium transition"
                    @disabled($candidatosCount === 0)>
                    Redistribuir ahora
                </button>
            </form>
        </div>
    @endif

    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-800 text-gray-400 text-sm uppercase">
                    <tr>
                        <th class="p-4">Lead</th>
                        <th class="p-4 hidden md:table-cell">Teléfono</th>
                        <th class="p-4">De</th>
                        <th class="p-4">A</th>
                        <th class="p-4 hidden lg:table-cell">Reasignado por</th>
                        <th class="p-4">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reassignments as $reassignment)
                        <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition">
                            <td class="p-4">{{ $reassignment->lead->full_name ?: ($reassignment->lead->meta_lead_id ?: 'Sin nombre') }}</td>
                            <td class="p-4 hidden md:table-cell">{{ $reassignment->lead->phone_number ?: '—' }}</td>
                            <td class="p-4">{{ $reassignment->fromConcesionario->nombre ?? '—' }}</td>
                            <td class="p-4">{{ $reassignment->toConcesionario->nombre ?? '—' }}</td>
                            <td class="p-4 hidden lg:table-cell">{{ $reassignment->reassignedBy->name ?? '—' }}</td>
                            <td class="p-4 whitespace-nowrap">{{ $reassignment->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">
                                Todavía no se ha repartido ningún lead.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reassignments->hasPages())
            <div class="p-5 border-t border-gray-800">
                {{ $reassignments->links() }}
            </div>
        @endif
    </div>

</div>

@endsection
