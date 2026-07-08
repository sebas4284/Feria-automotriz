<?php

namespace App\Http\Controllers;

use App\Models\Concesionario;
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

        Concesionario::create($request->all());

        return redirect()
            ->route('concesionarios.index')
            ->with(
                'success',
                'Concesionario creado correctamente'
            );
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
        'activo' => $request->has('activo')
    ]);

    return redirect()
        ->route('concesionarios.index')
        ->with(
            'success',
            'Concesionario actualizado correctamente'
        );
}

    public function destroy(
        Concesionario $concesionario
    )
    {
        $concesionario->delete();

        return back();
    }
}