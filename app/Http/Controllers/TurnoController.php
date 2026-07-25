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

    /**
     * Confirma manualmente a quién le tocó el turno actual. Es un ajuste
     * excepcional del staff, independiente del registro de clientes (que
     * sigue asignando automático vía TurnoAssignmentService::nextConcesionario()).
     * concesionario_id puede ser el "siguiente" sugerido (si sí está) o el
     * que queda "detrás" (si el sugerido no está en ese momento).
     */
    public function rotar(Request $request, TurnoAssignmentService $turnos)
    {
        $request->validate([
            'concesionario_id' => 'required|exists:concesionarios,id',
        ]);

        $concesionario = Concesionario::findOrFail($request->concesionario_id);

        $tieneTurnoHoy = Turno::where('concesionario_id', $concesionario->id)
            ->whereDate('fecha', today())
            ->exists();

        if (! $tieneTurnoHoy) {
            return redirect()->route('turnos.index')->with('error', 'Ese concesionario no está en la fila de hoy.');
        }

        $turnos->registrarAsignacion($concesionario);

        return redirect()->route('turnos.index')->with('success', "Turno confirmado para {$concesionario->nombre}.");
    }

    /**
     * Vista de solo lectura pensada para una pantalla grande/TV en el
     * punto de atención: muestra el último cliente sin cita registrado y
     * el concesionario que le corresponde, en letras enormes.
     */
    public function pantalla()
    {
        $ultimoCliente = Cliente::with('concesionario')
            ->where('cita', false)
            ->whereDate('created_at', today())
            ->latest()
            ->first();

        return view('turnos.pantalla', compact('ultimoCliente'));
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
