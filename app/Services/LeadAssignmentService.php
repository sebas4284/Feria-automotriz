<?php

namespace App\Services;

use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\LeadReassignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    /**
     * Reparte en round-robin los leads vencidos o sin asesor (basta con que
     * cumplan una de las dos condiciones) de todo el sistema entre los
     * concesionarios indicados (en el orden dado), sin importar a cuál
     * concesionario pertenecen actualmente.
     */
    public function redistribuirVencidosSinAsesor(array $nombresConcesionarios, User $por, string $motivo): \Illuminate\Support\Collection
    {
        $objetivos = $this->resolverObjetivosONinguno($nombresConcesionarios);

        $candidatos = Lead::vencidoOSinAsesor()->oldest('assigned_at')->get();

        $loteId = (string) Str::uuid();

        return $candidatos->values()->map(function (Lead $lead, int $i) use ($objetivos, $por, $motivo, $loteId) {
            $destino = $objetivos[$i % $objetivos->count()];

            // Si el turno del round-robin le tocaría al concesionario donde el lead ya
            // está (p. ej. al re-correr sobre leads que ya quedaron parejos), se pasa al
            // siguiente del ciclo — "redistribuir" siempre debe mover a otro distinto.
            if ($destino->id === $lead->concesionario_id) {
                $destino = $objetivos[($i + 1) % $objetivos->count()];
            }

            $this->reassign($lead, $destino, $por, $motivo, $loteId);

            return $lead->fresh();
        });
    }

    /**
     * Resuelve cada nombre configurado a exactamente un concesionario activo,
     * en el mismo orden dado. Si algún nombre no matchea ningún concesionario
     * activo, o matchea más de uno (nombres duplicados, p. ej. por la
     * sincronización con el sheet), lanza en vez de repartir con una lista
     * incompleta o desalineada — así el round-robin nunca colapsa en
     * silencio a menos destinos de los configurados.
     */
    private function resolverObjetivosONinguno(array $nombresConcesionarios): \Illuminate\Support\Collection
    {
        $porNombre = Concesionario::where('activo', true)
            ->whereIn('nombre', $nombresConcesionarios)
            ->get()
            ->groupBy('nombre');

        $problemas = [];

        foreach ($nombresConcesionarios as $nombre) {
            $cantidad = $porNombre->get($nombre, collect())->count();

            if ($cantidad === 0) {
                $problemas[] = "\"{$nombre}\" (no existe ningún concesionario activo con ese nombre exacto)";
            } elseif ($cantidad > 1) {
                $problemas[] = "\"{$nombre}\" ({$cantidad} concesionarios activos coinciden con ese nombre)";
            }
        }

        if (! empty($problemas)) {
            throw new \RuntimeException(
                'No se pudo redistribuir, revisa en Concesionarios: '.implode('; ', $problemas).'.'
            );
        }

        return collect($nombresConcesionarios)->map(fn (string $nombre) => $porNombre->get($nombre)->first())->values();
    }

    public function reassign(Lead $lead, Concesionario $to, User $by, ?string $motivo = null, ?string $loteId = null): void
    {
        DB::transaction(function () use ($lead, $to, $by, $motivo, $loteId) {
            LeadReassignment::create([
                'lead_id' => $lead->id,
                'from_concesionario_id' => $lead->concesionario_id,
                'to_concesionario_id' => $to->id,
                'reassigned_by' => $by->id,
                'motivo' => $motivo,
                'lote_id' => $loteId,
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
