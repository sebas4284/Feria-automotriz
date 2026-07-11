<?php

namespace App\Http\Controllers;

use App\Models\AsesorComercial;
use App\Models\Concesionario;
use App\Models\Lead;
use App\Services\LeadAssignmentService;
use App\Services\LeadNotifier;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with('concesionario')->visibleTo($request->user())->latest('created_time');

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
        }

        $leads = $query->get();

        $totalNuevos = $leads->filter(
            fn (Lead $lead) => ($lead->created_time ?? $lead->created_at)?->isToday()
        )->count();
        $totalVencidos = $leads->filter(fn (Lead $lead) => $lead->vencido)->count();

        $concesionarios = Concesionario::where('activo', true)->orderBy('nombre')->get();

        $asesoresPorConcesionario = AsesorComercial::orderBy('nombre')->get()->groupBy('concesionario_id');

        return view(
            'leads.index',
            compact('leads', 'totalNuevos', 'totalVencidos', 'concesionarios', 'asesoresPorConcesionario')
        );
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
            'estado_gestion' => 'required|in:Nuevo,Contactado,Negociacion,Vendido,Perdido',
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

        $lead->update(['asesor_comercial_id' => $asesor->id]);

        $notifier->notifyAsesor($asesor, $lead->fresh());

        return back()->with('success', 'Lead asignado a ' . $asesor->nombre);
    }
}
