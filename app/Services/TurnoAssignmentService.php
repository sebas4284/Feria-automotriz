<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Concesionario;
use App\Models\Turno;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TurnoAssignmentService
{
    public function checkIn(Concesionario $concesionario): Turno
    {
        return Turno::firstOrCreate(
            ['concesionario_id' => $concesionario->id, 'fecha' => today()],
            ['llegada_at' => now()]
        );
    }

    public function checkOut(Concesionario $concesionario): void
    {
        Turno::where('concesionario_id', $concesionario->id)
            ->whereDate('fecha', today())
            ->delete();
    }

    /**
     * Elige el concesionario que corresponde según turno: fila estricta por
     * orden de llegada. Quien nunca ha sido atendido hoy espera desde que
     * llegó; a quien ya le tocó, se le cuenta la espera desde la última vez
     * que fue atendido — así, al ser atendido, pasa al final de la fila y
     * el ciclo se repite (A, B, C, A, B, C...).
     */
    public function nextConcesionario(): ?Concesionario
    {
        return $this->proximos(1)->first();
    }

    /**
     * Los siguientes $cantidad concesionarios en la fila, en el mismo orden
     * estricto que usa nextConcesionario() (para mostrar "en turno" y
     * "se prepara" en la pantalla exterior, por ejemplo).
     */
    public function proximos(int $cantidad): Collection
    {
        return $this->filaQuery()->limit($cantidad)->get()->map->concesionario;
    }

    private function filaQuery()
    {
        return Turno::with('concesionario')
            ->whereDate('fecha', today())
            ->whereHas('concesionario', fn ($q) => $q->where('activo', true))
            ->orderByRaw('COALESCE(ultima_asignacion_at, llegada_at) ASC')
            ->orderBy('id');
    }

    public function registrarAsignacion(Concesionario $concesionario): void
    {
        $turno = Turno::where('concesionario_id', $concesionario->id)
            ->whereDate('fecha', today())
            ->first();

        if (! $turno) {
            return;
        }

        $turno->update([
            'ultima_asignacion_at_previa' => $turno->ultima_asignacion_at,
            'tiene_asignacion_deshacible' => true,
            'ultima_asignacion_at' => now(),
            'veces_asignado' => $turno->veces_asignado + 1,
            'veces_procesado' => $turno->veces_procesado + 1,
        ]);
    }

    /**
     * Salta el turno de este concesionario sin registrarle una asignación
     * real: lo manda al final de la fila (misma mecánica que ser atendido)
     * pero sin sumar a veces_asignado, para no inflar sus estadísticas de
     * atención real. Sí cuenta para el conteo de rondas. Invalida cualquier
     * "deshacer" pendiente de una asignación anterior, ya que la fila volvió
     * a moverse después de esa asignación.
     */
    public function enviarAlFinal(Concesionario $concesionario): void
    {
        Turno::where('concesionario_id', $concesionario->id)
            ->whereDate('fecha', today())
            ->update([
                'ultima_asignacion_at' => now(),
                'ultima_asignacion_at_previa' => null,
                'tiene_asignacion_deshacible' => false,
                'veces_procesado' => DB::raw('veces_procesado + 1'),
            ]);
    }

    /**
     * Deshace la última asignación real de este concesionario: restaura su
     * posición de turno anterior y desasigna al cliente que se le había
     * asignado (queda oculto de las listas de Turnos, pero no se borra).
     * No hace nada si la última acción no fue una asignación (p. ej. si
     * después se le saltó el turno).
     */
    public function deshacerAsignacion(Concesionario $concesionario): void
    {
        $turno = Turno::where('concesionario_id', $concesionario->id)
            ->whereDate('fecha', today())
            ->first();

        if (! $turno || ! $turno->tiene_asignacion_deshacible) {
            return;
        }

        Cliente::where('concesionario_id', $concesionario->id)
            ->where('cita', false)
            ->whereDate('created_at', today())
            ->latest('updated_at')
            ->first()
            ?->update(['concesionario_id' => null, 'oculto_en_turnos' => true]);

        $turno->update([
            'ultima_asignacion_at' => $turno->ultima_asignacion_at_previa,
            'ultima_asignacion_at_previa' => null,
            'tiene_asignacion_deshacible' => false,
            'veces_asignado' => max(0, $turno->veces_asignado - 1),
            'veces_procesado' => max(0, $turno->veces_procesado - 1),
        ]);
    }

    /**
     * Número de ronda actual: cuántas vueltas completas ha dado la fila.
     * Ronda #1 mientras nadie ha completado una vuelta; sube en cuanto el
     * concesionario más atrasado (el de menor veces_procesado) es procesado
     * (asignado o saltado) y con eso todos alcanzan el mismo número de
     * vueltas. 0 si todavía no hay nadie en la fila de hoy.
     */
    public function rondaActual(): int
    {
        $minimo = Turno::whereDate('fecha', today())
            ->whereHas('concesionario', fn ($q) => $q->where('activo', true))
            ->min('veces_procesado');

        return $minimo === null ? 0 : $minimo + 1;
    }
}
