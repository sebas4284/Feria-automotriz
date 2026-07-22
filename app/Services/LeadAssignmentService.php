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
     * Elige un concesionario activo al azar, con probabilidad proporcional a su
     * peso_asignacion. A diferencia de "dárselo siempre al más atrasado", esto
     * evita que un concesionario acapare el 100% del reparto mientras se pone
     * al día con el histórico de los demás: todos reciben leads desde el primer
     * momento, y la proporción de pesos se cumple en el agregado a largo plazo.
     */
    public function assignNext(): ?Concesionario
    {
        $activos = Concesionario::where('activo', true)
            ->where('peso_asignacion', '>', 0)
            ->get();

        if ($activos->isEmpty()) {
            return null;
        }

        $pesoTotal = $activos->sum('peso_asignacion');
        $punto = mt_rand(1, $pesoTotal);

        $acumulado = 0;

        foreach ($activos as $concesionario) {
            $acumulado += $concesionario->peso_asignacion;

            if ($punto <= $acumulado) {
                return $concesionario;
            }
        }

        return $activos->last();
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
