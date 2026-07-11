<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Concesionario;
use App\Models\Turno;
use App\Services\TurnoAssignmentService;

class TurnoController extends Controller
{
    public function index(TurnoAssignmentService $turnos)
    {
        $concesionarios = Concesionario::where('activo', true)->orderBy('nombre')->get();

        $turnosHoy = Turno::whereDate('fecha', today())->get()->keyBy('concesionario_id');

        $siguiente = $turnos->nextConcesionario();

        $enFila = $concesionarios
            ->filter(fn (Concesionario $c) => $turnosHoy->has($c->id))
            ->sortBy(function (Concesionario $c) use ($turnosHoy) {
                $t = $turnosHoy->get($c->id);

                return [$t->veces_asignado, $t->llegada_at->timestamp, $t->id];
            })
            ->values();

        $clientesHoy = Cliente::with('concesionario')
            ->where('cita', false)
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return view('turnos.index', compact('concesionarios', 'turnosHoy', 'siguiente', 'enFila', 'clientesHoy'));
    }

    public function checkIn(Concesionario $concesionario, TurnoAssignmentService $turnos)
    {
        $turnos->checkIn($concesionario);

        return back()->with('success', "{$concesionario->nombre} marcado como llegado");
    }

    public function checkOut(Concesionario $concesionario, TurnoAssignmentService $turnos)
    {
        $turnos->checkOut($concesionario);

        return back()->with('success', "{$concesionario->nombre} quitado de la fila de hoy");
    }
}
