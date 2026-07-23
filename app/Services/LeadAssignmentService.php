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
     * Elige el próximo concesionario activo con "smooth weighted round-robin"
     * (el algoritmo de balanceo de Nginx): cada concesionario acumula su peso
     * en swrr_current_weight en cada llamada, se elige el de mayor acumulado,
     * y a ese se le resta el peso total. Esto intercala las asignaciones según
     * el peso configurado sin la varianza de una selección al azar: todos
     * reciben leads regularmente y las proporciones se cumplen desde el
     * principio, no solo en el agregado a largo plazo.
     */
    public function assignNext(): ?Concesionario
    {
        return DB::transaction(function () {
            $activos = Concesionario::where('activo', true)
                ->where('peso_asignacion', '>', 0)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($activos->isEmpty()) {
                return null;
            }

            $pesoTotal = $activos->sum('peso_asignacion');

            $seleccionado = null;

            foreach ($activos as $concesionario) {
                $concesionario->swrr_current_weight += $concesionario->peso_asignacion;

                if ($seleccionado === null || $concesionario->swrr_current_weight > $seleccionado->swrr_current_weight) {
                    $seleccionado = $concesionario;
                }
            }

            $seleccionado->swrr_current_weight -= $pesoTotal;

            foreach ($activos as $concesionario) {
                $concesionario->save();
            }

            return $seleccionado;
        });
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
