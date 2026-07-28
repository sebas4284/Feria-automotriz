<?php

namespace App\Http\Controllers;

use App\Models\Concesionario;
use App\Models\Vehiculo;
use Illuminate\Http\Request;

class ConcesionarioController extends Controller
{
    public function index()
    {
        $concesionarios = Concesionario::latest()->get();

        return view(
            'concesionarios.index',
            compact('concesionarios')
        );
    }

    public function create()
    {
        return view('concesionarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required'
        ]);

        $data = $request->all();
        $data['cupo_feria'] = $request->cupo_feria !== null && $request->cupo_feria !== '' ? $request->cupo_feria : null;

        Concesionario::create($data);

        return redirect()
            ->route('concesionarios.index')
            ->with(
                'success',
                'Concesionario creado correctamente'
            );
    }

    public function show(Concesionario $concesionario)
    {
        return view('concesionarios.show', compact('concesionario'));
    }

    public function edit(Concesionario $concesionario)
{
    return view(
        'concesionarios.edit',
        compact('concesionario')
    );
}

    public function update(
    Request $request,
    Concesionario $concesionario
)
{
    $request->validate([
        'nombre' => 'required'
    ]);

    $concesionario->update([
        'nombre' => $request->nombre,
        'nit' => $request->nit,
        'ciudad' => $request->ciudad,
        'telefono' => $request->telefono,
        'email' => $request->email,
        'responsable' => $request->responsable,
        'peso_asignacion' => $request->peso_asignacion,
        'cupo_feria' => $request->cupo_feria !== null && $request->cupo_feria !== '' ? $request->cupo_feria : null,
        'activo' => $request->has('activo')
    ]);

    $demovidos = $this->demoverExcedentes($concesionario);

    $mensaje = 'Concesionario actualizado correctamente';
    if ($demovidos > 0) {
        $mensaje .= ". Se movieron {$demovidos} vehículo(s) a \"Fuera del área\" por la reducción de cupo.";
    }

    return redirect()
        ->route('concesionarios.index')
        ->with(
            'success',
            $mensaje
        );
}

private function demoverExcedentes(Concesionario $concesionario): int
{
    if ($concesionario->cupo_feria === null) {
        return 0;
    }

    $usado = Vehiculo::where('concesionario_id', $concesionario->id)
        ->where('ubicacion', 'Dentro del área')
        ->where('estado', '!=', 'Vendido')
        ->count();

    $exceso = $usado - $concesionario->cupo_feria;

    if ($exceso <= 0) {
        return 0;
    }

    $candidatos = Vehiculo::where('concesionario_id', $concesionario->id)
        ->where('ubicacion', 'Dentro del área')
        ->where('estado', '!=', 'Vendido')
        ->whereNull('ingresado_at')
        ->orderByDesc('created_at')
        ->limit($exceso)
        ->get();

    foreach ($candidatos as $vehiculo) {
        $vehiculo->update(['ubicacion' => 'Fuera del área']);
    }

    return $candidatos->count();
}

    public function destroy(
        Concesionario $concesionario
    )
    {
        $concesionario->delete();

        return back();
    }
}