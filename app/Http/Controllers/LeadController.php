<?php

namespace App\Http\Controllers;

use App\Models\AsesorComercial;
use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\LeadReassignment;
use App\Services\LeadAssignmentService;
use App\Services\LeadNotifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public const REDISTRIBUCION_MOTIVO = 'Redistribución: vencido sin asesor';

    private function filteredLeadsQuery(Request $request): Builder
    {
        $query = Lead::with(['concesionario', 'asesorComercial'])->visibleTo($request->user())->latest('created_time');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('full_name', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('phone_number', 'like', "%{$buscar}%");
            });
        }

        if ($request->query('filtro') === 'vencido') {
            $query->vencido();
        } elseif ($request->query('filtro') === 'sin_asesor') {
            $query->whereNull('asesor_comercial_id');
        } elseif ($request->query('filtro') === 'contactado') {
            $query->where('estado_gestion', 'Contactado');
        }

        if ($request->user()->isAdmin() && $request->filled('concesionario_id')) {
            $query->where('concesionario_id', $request->concesionario_id);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $leads = $this->filteredLeadsQuery($request)->paginate(50)->withQueryString();

        $totalNuevos = $this->filteredLeadsQuery($request)
            ->whereRaw('DATE(COALESCE(created_time, created_at)) = ?', [now()->toDateString()])
            ->count();
        $totalVencidos = $this->filteredLeadsQuery($request)->vencido()->count();
        $totalSinAsesor = $this->filteredLeadsQuery($request)->whereNull('asesor_comercial_id')->count();
        $totalContactados = $this->filteredLeadsQuery($request)->where('estado_gestion', 'Contactado')->count();

        $concesionarios = Concesionario::where('activo', true)->orderBy('nombre')->get();

        $asesoresPorConcesionario = AsesorComercial::orderBy('nombre')->get()->groupBy('concesionario_id');

        return view(
            'leads.index',
            compact('leads', 'totalNuevos', 'totalVencidos', 'totalSinAsesor', 'totalContactados', 'concesionarios', 'asesoresPorConcesionario')
        );
    }

    public function redistribucion(Request $request)
    {
        $baseQuery = fn () => LeadReassignment::where('motivo', self::REDISTRIBUCION_MOTIVO)
            ->visibleTo($request->user());

        // orderByDesc('id') en vez de latest(): created_at solo tiene precisión de
        // segundo, y un reparto de muchos leads puede insertar varias filas en el
        // mismo segundo — el id autoincremental sí refleja el orden real de inserción.
        $ultimoLoteId = $baseQuery()->orderByDesc('id')->value('lote_id');

        $mostrandoHistorial = $request->boolean('historial') || $ultimoLoteId === null;

        $reassignments = $baseQuery()
            ->with(['lead.concesionario', 'fromConcesionario', 'toConcesionario', 'reassignedBy'])
            ->when(! $mostrandoHistorial, fn ($query) => $query->where('lote_id', $ultimoLoteId))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $candidatosCount = null;
        $totalVencidos = null;
        $totalSinAsesor = null;
        $resolucionObjetivos = null;

        if ($request->user()->isAdmin()) {
            $candidatosCount = Lead::vencidoOSinAsesor()->count();
            $totalVencidos = Lead::vencido()->count();
            $totalSinAsesor = Lead::whereNull('asesor_comercial_id')->count();
            $resolucionObjetivos = $this->resolucionObjetivosRedistribucion();
        }

        return view('leads.redistribucion', compact(
            'reassignments', 'candidatosCount', 'totalVencidos', 'totalSinAsesor', 'resolucionObjetivos', 'mostrandoHistorial'
        ));
    }

    public function redistribuirVencidos(Request $request, LeadAssignmentService $service, LeadNotifier $notifier)
    {
        $this->authorize('redistribuirVencidos', Lead::class);

        try {
            $leads = $service->redistribuirVencidosSinAsesor(
                config('leads.redistribution.target_concesionarios'),
                $request->user(),
                self::REDISTRIBUCION_MOTIVO
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($leads->isEmpty()) {
            return back()->with('success', 'No hay leads vencidos sin asesor para redistribuir.');
        }

        $resumen = $leads->groupBy('concesionario_id')->map(function ($grupo) use ($notifier) {
            $concesionario = $grupo->first()->concesionario;
            $notifier->notifyLoteRedistribuido($concesionario, $grupo->count());

            return "{$concesionario->nombre}: {$grupo->count()}";
        })->implode(', ');

        return back()->with('success', "Se redistribuyeron {$leads->count()} leads ({$resumen}).");
    }

    /**
     * Para mostrar en la pantalla, antes de ejecutar nada: qué concesionario
     * activo matchea (o no) cada nombre configurado ahora mismo, así un
     * nombre desalineado o inactivo se nota a simple vista antes de repartir.
     */
    private function resolucionObjetivosRedistribucion(): \Illuminate\Support\Collection
    {
        $nombres = config('leads.redistribution.target_concesionarios');

        $porNombre = Concesionario::where('activo', true)
            ->whereIn('nombre', $nombres)
            ->get()
            ->groupBy('nombre');

        return collect($nombres)->map(fn (string $nombre) => [
            'nombre' => $nombre,
            'cantidad' => $porNombre->get($nombre, collect())->count(),
        ]);
    }

    public function show(Lead $lead)
    {
        $this->authorize('view', $lead);

        $lead->load(['concesionario', 'asesorComercial', 'reassignments.fromConcesionario', 'reassignments.toConcesionario', 'reassignments.reassignedBy']);

        $concesionarios = Concesionario::where('activo', true)->orderBy('nombre')->get();

        $asesores = $lead->concesionario_id
            ? AsesorComercial::where('concesionario_id', $lead->concesionario_id)->orderBy('nombre')->get()
            : collect();

        return view('leads.show', compact('lead', 'concesionarios', 'asesores'));
    }

    public function edit(Lead $lead)
    {
        $this->authorize('update', $lead);

        return view('leads.edit', compact('lead'));
    }

    public function update(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'estado_gestion' => 'required|in:Nuevo,Asignado,Contactado,Negociacion,Vendido,Perdido',
            'observaciones' => 'nullable|string',
        ]);

        $lead->update($validated);

        return redirect()->route('leads.show', $lead)->with('success', 'Lead actualizado correctamente');
    }

    public function destroy(Lead $lead)
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead eliminado correctamente');
    }

    public function reassign(Request $request, Lead $lead, LeadAssignmentService $service, LeadNotifier $notifier)
    {
        $this->authorize('reassign', $lead);

        $data = $request->validate([
            'to_concesionario_id' => 'required|exists:concesionarios,id',
            'motivo' => 'nullable|string|max:500',
        ]);

        $to = Concesionario::where('activo', true)->findOrFail($data['to_concesionario_id']);

        $service->reassign($lead, $to, $request->user(), $data['motivo'] ?? null);

        $notifier->notifyConcesionario($to, $lead->fresh());

        return back()->with('success', 'Lead reasignado correctamente');
    }

    public function assignAsesor(Request $request, Lead $lead, LeadNotifier $notifier)
    {
        $this->authorize('assignAsesor', $lead);

        $data = $request->validate([
            'asesor_comercial_id' => 'required|exists:asesores_comerciales,id',
        ]);

        $asesor = AsesorComercial::where('concesionario_id', $lead->concesionario_id)
            ->findOrFail($data['asesor_comercial_id']);

        $lead->update([
            'asesor_comercial_id' => $asesor->id,
            'estado_gestion' => $lead->estado_gestion === 'Nuevo' ? 'Asignado' : $lead->estado_gestion,
            'assigned_at' => now(),
            'vencido_notified_at' => null,
        ]);

        $notifier->notifyAsesor($asesor, $lead->fresh());

        return back()->with('success', 'Lead asignado a ' . $asesor->nombre);
    }
}
