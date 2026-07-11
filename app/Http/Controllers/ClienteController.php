<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Concesionario;
use App\Services\TurnoAssignmentService;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    private const MEDIOS = [
        'Redes sociales',
        'Referido',
        'Feria/Evento',
        'Publicidad o pagina web',
        'No se',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Cliente::class);

        $query = Cliente::with('concesionario')->visibleTo($request->user())->latest();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        $clientes = $query->get();

        return view('clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Cliente::class);

        $concesionarios = $this->concesionariosDisponibles();
        $medios = self::MEDIOS;

        return view('clientes.create', compact('concesionarios', 'medios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, TurnoAssignmentService $turnos)
    {
        $this->authorize('create', Cliente::class);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'cita' => 'sometimes|boolean',
            'concesionario_id' => 'nullable|exists:concesionarios,id',
            'medio_entero' => 'nullable|in:' . implode(',', self::MEDIOS),
            'observaciones' => 'nullable|string',
        ]);

        $tieneCita = $request->boolean('cita');

        if ($tieneCita) {
            $concesionarioId = $this->resolveConcesionarioId($request);
        } else {
            $concesionario = $turnos->nextConcesionario();
            $concesionarioId = $concesionario?->id;

            if ($concesionario) {
                $turnos->registrarAsignacion($concesionario);
            }
        }

        Cliente::create([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'cita' => $tieneCita,
            'concesionario_id' => $concesionarioId,
            'medio_entero' => $request->medio_entero,
            'observaciones' => $request->observaciones,
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente creado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        $this->authorize('view', $cliente);

        $cliente->load('concesionario');

        return view('clientes.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        $this->authorize('update', $cliente);

        $concesionarios = $this->concesionariosDisponibles();
        $medios = self::MEDIOS;

        return view('clientes.edit', compact('cliente', 'concesionarios', 'medios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $this->authorize('update', $cliente);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'cita' => 'sometimes|boolean',
            'concesionario_id' => 'nullable|exists:concesionarios,id',
            'medio_entero' => 'nullable|in:' . implode(',', self::MEDIOS),
            'observaciones' => 'nullable|string',
        ]);

        $cliente->update([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'cita' => $request->boolean('cita'),
            'concesionario_id' => $this->resolveConcesionarioId($request),
            'medio_entero' => $request->medio_entero,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('success', 'Cliente actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        $this->authorize('delete', $cliente);

        $cliente->delete();

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente eliminado correctamente');
    }

    private function concesionariosDisponibles()
    {
        $user = auth()->user();

        return $user->isAdmin()
            ? Concesionario::where('activo', true)->orderBy('nombre')->get()
            : Concesionario::where('id', $user->concesionario_id)->get();
    }

    private function resolveConcesionarioId(Request $request): ?int
    {
        $user = $request->user();

        return $user->isAdmin() ? $request->concesionario_id : $user->concesionario_id;
    }
}
