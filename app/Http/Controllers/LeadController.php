<?php

namespace App\Http\Controllers;

use App\Models\Concesionario;
use App\Models\Lead;
use App\Services\LeadAssignmentService;
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

        $totalNuevos = $leads->where('estado_gestion', 'Nuevo')->count();
        $totalVencidos = $leads->filter(fn (Lead $lead) => $lead->vencido)->count();

        $concesionarios = Concesionario::where('activo', true)->orderBy('nombre')->get();

        return view(
            'leads.index',
            compact('leads', 'totalNuevos', 'totalVencidos', 'concesionarios')
        );
    }

    public function show(Lead $lead)
    {
        $this->authorize('view', $lead);

        $lead->load(['concesionario', 'reassignments.fromConcesionario', 'reassignments.toConcesionario', 'reassignments.reassignedBy']);

        $concesionarios = Concesionario::where('activo', true)->orderBy('nombre')->get();

        return view('leads.show', compact('lead', 'concesionarios'));
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

    public function reassign(Request $request, Lead $lead, LeadAssignmentService $service)
    {
        $this->authorize('reassign', $lead);

        $data = $request->validate([
            'to_concesionario_id' => 'required|exists:concesionarios,id',
            'motivo' => 'nullable|string|max:500',
        ]);

        $to = Concesionario::where('activo', true)->findOrFail($data['to_concesionario_id']);

        $service->reassign($lead, $to, $request->user(), $data['motivo'] ?? null);

        return back()->with('success', 'Lead reasignado correctamente');
    }
}
