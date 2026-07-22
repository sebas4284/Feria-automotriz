<?php

namespace App\Services;

use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\LeadReassignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeadAssignmentService
{
    /**
     * Elige el concesionario activo con menor ratio (leads_asignados / peso_asignacion),
     * de forma que el reparto converja proporcionalmente a los pesos configurados.
     */
    public function assignNext(): ?Concesionario
    {
        $activos = Concesionario::where('activo', true)
            ->where('peso_asignacion', '>', 0)
            ->get();

        if ($activos->isEmpty()) {
            return null;
        }

        $ventana = now()->subDays((int) config('leads.assignment_window_days'));

        $counts = Lead::whereIn('concesionario_id', $activos->pluck('id'))
            ->where('assigned_at', '>=', $ventana)
            ->selectRaw('concesionario_id, count(*) as total')
            ->groupBy('concesionario_id')
            ->pluck('total', 'concesionario_id');

        return $activos
            ->sortBy(fn (Concesionario $c) => [
                ($counts[$c->id] ?? 0) / $c->peso_asignacion,
                $c->id,
            ])
            ->first();
    }

    public function reassign(Lead $lead, Concesionario $to, User $by, ?string $motivo = null): void
    {
        DB::transaction(function () use ($lead, $to, $by, $motivo) {
            LeadReassignment::create([
                'lead_id' => $lead->id,
                'from_concesionario_id' => $lead->concesionario_id,
                'to_concesionario_id' => $to->id,
                'reassigned_by' => $by->id,
                'motivo' => $motivo,
            ]);

            $lead->update([
                'concesionario_id' => $to->id,
                'assigned_at' => now(),
                'asesor_comercial_id' => null,
                'estado_gestion' => $lead->estado_gestion === 'Asignado' ? 'Nuevo' : $lead->estado_gestion,
                'vencido_notified_at' => null,
            ]);
        });
    }
}
