<?php

namespace App\Services;

use App\Models\Concesionario;
use App\Models\Turno;
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
        $turno = Turno::with('concesionario')
            ->whereDate('fecha', today())
            ->whereHas('concesionario', fn ($q) => $q->where('activo', true))
            ->orderByRaw('COALESCE(ultima_asignacion_at, llegada_at) ASC')
            ->orderBy('id')
            ->first();

        return $turno?->concesionario;
    }

    public function registrarAsignacion(Concesionario $concesionario): void
    {
        Turno::where('concesionario_id', $concesionario->id)
            ->whereDate('fecha', today())
            ->update([
                'ultima_asignacion_at' => now(),
                'veces_asignado' => DB::raw('veces_asignado + 1'),
            ]);
    }
}
