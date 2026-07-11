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
     * Elige el concesionario que corresponde según turno: el que menos
     * clientes ha recibido hoy (empate: quien llegó primero, luego por id).
     * Se ordena por un contador entero en vez de por hora exacta para que
     * la rotación sea determinística aunque varias asignaciones ocurran
     * dentro del mismo segundo.
     */
    public function nextConcesionario(): ?Concesionario
    {
        $turno = Turno::with('concesionario')
            ->whereDate('fecha', today())
            ->whereHas('concesionario', fn ($q) => $q->where('activo', true))
            ->orderBy('veces_asignado')
            ->orderBy('llegada_at')
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
