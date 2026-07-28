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

                return [($t->ultima_asignacion_at ?? $t->llegada_at)->timestamp, $t->id];
            })
            ->values();

        $sinTurno = $concesionarios->reject(fn (Concesionario $c) => $turnosHoy->has($c->id))->values();
        $concesionariosOrdenados = $enFila->concat($sinTurno);

        $clientesHoy = Cliente::with('concesionario')
            ->where('cita', false)
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        $pendientes = $clientesHoy->whereNull('concesionario_id')->values();

        return view('turnos.index', compact(
            'concesionarios', 'concesionariosOrdenados', 'turnosHoy', 'siguiente',
            'enFila', 'clientesHoy', 'pendientes'
        ));
    }

    /**
     * Asigna un cliente pendiente (sin cita, sin concesionario) a uno
     * específico — se dispara al soltarlo (drag & drop) sobre su tarjeta
     * en /turnos. Avanza la cola de ese concesionario igual que "rotar()".
     */
    public function asignarCliente(Request $request, TurnoAssignmentService $turnos)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'concesionario_id' => 'required|exists:concesionarios,id',
        ]);

        $concesionario = Concesionario::findOrFail($request->concesionario_id);

        $tieneTurnoHoy = Turno::where('concesionario_id', $concesionario->id)
            ->whereDate('fecha', today())
            ->exists();

        if (! $tieneTurnoHoy) {
            return response()->json(['message' => 'Ese concesionario no está en la fila de hoy.'], 422);
        }

        $cliente = Cliente::findOrFail($request->cliente_id);
        $cliente->update(['concesionario_id' => $concesionario->id]);
        $turnos->registrarAsignacion($concesionario);

        return response()->json(['message' => "{$cliente->nombre} asignado a {$concesionario->nombre}."]);
    }

    /**
     * Vista de solo lectura pensada para una pantalla grande/TV en el
     * punto de atención: muestra las últimas asignaciones del día
     * (concesionario + cliente), la más reciente resaltada arriba.
     */
    public function pantalla()
    {
        $asignaciones = Cliente::with('concesionario')
            ->where('cita', false)
            ->whereNotNull('concesionario_id')
            ->whereDate('created_at', today())
            ->latest()
            ->limit(8)
            ->get();

        return view('turnos.pantalla', compact('asignaciones'));
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
