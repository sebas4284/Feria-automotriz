<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Concesionario;
use App\Models\Turno;
use App\Services\TurnoAssignmentService;
use Illuminate\Http\Request;

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

        $detras = $enFila->get(1);
        $rondaSiguiente = $siguiente ? $turnosHoy->get($siguiente->id)->veces_asignado + 1 : null;
        $rondaDetras = $detras ? $turnosHoy->get($detras->id)->veces_asignado + 1 : null;

        $clientesHoy = Cliente::with('concesionario')
            ->where('cita', false)
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return view('turnos.index', compact(
            'concesionarios', 'turnosHoy', 'siguiente', 'detras',
            'rondaSiguiente', 'rondaDetras', 'enFila', 'clientesHoy'
        ));
    }

    public function rotar(Request $request, TurnoAssignmentService $turnos)
    {
        $request->validate([
            'concesionario_id' => 'required|exists:concesionarios,id',
        ]);

        $siguiente = $turnos->nextConcesionario();

        if (! $siguiente || $siguiente->id !== (int) $request->concesionario_id) {
            return back()->with('error', 'El turno cambió antes de confirmar. Revisa quién sigue e inténtalo de nuevo.');
        }

        $turnos->registrarAsignacion($siguiente);

        return redirect()
            ->route('clientes.create', ['concesionario_id' => $siguiente->id, 'cita' => 0])
            ->with('success', "Turno confirmado para {$siguiente->nombre}. Registra los datos del cliente.");
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
