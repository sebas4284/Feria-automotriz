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
        $rondaActual = $turnos->rondaActual();

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
            ->where('oculto_en_turnos', false)
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        $pendientes = $clientesHoy->whereNull('concesionario_id')->values();

        return view('turnos.index', compact(
            'concesionarios', 'concesionariosOrdenados', 'turnosHoy', 'siguiente',
            'enFila', 'clientesHoy', 'pendientes', 'rondaActual'
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
    public function pantalla(TurnoAssignmentService $turnos)
    {
        $asignaciones = Cliente::with('concesionario')
            ->where('cita', false)
            ->whereNotNull('concesionario_id')
            ->whereDate('created_at', today())
            ->latest()
            ->limit(8)
            ->get();

        $proximos = $turnos->proximos(2);
        $enTurno = $proximos->get(0);
        $sePrepara = $proximos->get(1);
        $rondaActual = $turnos->rondaActual();

        [$filaCompleta, $ordenLlegada] = $this->filaCompletaConOrden();

        return view('turnos.pantalla', compact('asignaciones', 'enTurno', 'sePrepara', 'rondaActual', 'filaCompleta', 'ordenLlegada'));
    }

    /**
     * Pantalla independiente (pensada para su propia TV/pestaña) que solo
     * muestra el orden fijo de llegada de todos los concesionarios de hoy —
     * separada de pantalla() porque con muchos concesionarios no cabía bien
     * junto a "En turno"/"Se prepara".
     */
    public function pantallaLlegadas()
    {
        [$filaCompleta, $ordenLlegada] = $this->filaCompletaConOrden();

        return view('turnos.pantalla-llegadas', compact('filaCompleta', 'ordenLlegada'));
    }

    /**
     * Todos los concesionarios con turno hoy, ordenados por hora de llegada
     * (orden fijo, no rota), junto con su número de orden ya calculado.
     */
    private function filaCompletaConOrden(): array
    {
        $filaCompleta = Turno::with('concesionario')
            ->whereDate('fecha', today())
            ->whereHas('concesionario', fn ($q) => $q->where('activo', true))
            ->orderBy('llegada_at')
            ->get();

        $ordenLlegada = $filaCompleta->values()->mapWithKeys(fn ($t, $i) => [$t->concesionario_id => $i + 1]);

        return [$filaCompleta, $ordenLlegada];
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

    /**
     * Salta el turno actual de este concesionario (p. ej. no estaba listo
     * cuando le tocaba) y lo manda al final de la fila, sin registrarle
     * una asignación real.
     */
    public function saltarTurno(Concesionario $concesionario, TurnoAssignmentService $turnos)
    {
        $turnos->enviarAlFinal($concesionario);

        return back()->with('success', "Se saltó el turno de {$concesionario->nombre}, pasa al final de la fila");
    }

    /**
     * Deshace la última asignación real de cliente de este concesionario:
     * recupera la posición de turno que tenía antes y desasigna al cliente
     * (sin borrarlo, solo lo oculta de las listas de Turnos).
     */
    public function deshacerAsignacion(Concesionario $concesionario, TurnoAssignmentService $turnos)
    {
        $turnos->deshacerAsignacion($concesionario);

        return back()->with('success', "Se deshizo la última asignación de {$concesionario->nombre}");
    }
}
