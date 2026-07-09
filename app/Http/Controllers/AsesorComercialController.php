<?php

namespace App\Http\Controllers;

use App\Models\AsesorComercial;
use App\Models\Concesionario;
use Illuminate\Http\Request;

class AsesorComercialController extends Controller
{
    public function index()
    {
        $asesores = AsesorComercial::with('concesionario')->latest()->get();

        return view('asesores.index', compact('asesores'));
    }

    public function create()
    {
        $this->authorize('create', AsesorComercial::class);

        $concesionarios = Concesionario::orderBy('nombre')->get();

        return view('asesores.create', compact('concesionarios'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', AsesorComercial::class);

        $request->validate([
            'cedula' => 'required|string|max:50|unique:asesores_comerciales,cedula',
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'concesionario_id' => 'required|exists:concesionarios,id',
        ]);

        AsesorComercial::create($request->all());

        return redirect()
            ->route('asesores.index')
            ->with('success', 'Asesor comercial creado correctamente');
    }

    public function show(AsesorComercial $asesor)
    {
        return view('asesores.show', compact('asesor'));
    }

    public function edit(AsesorComercial $asesor)
    {
        $this->authorize('update', $asesor);

        $concesionarios = Concesionario::orderBy('nombre')->get();

        return view('asesores.edit', compact('asesor', 'concesionarios'));
    }

    public function update(Request $request, AsesorComercial $asesor)
    {
        $this->authorize('update', $asesor);

        $request->validate([
            'cedula' => 'required|string|max:50|unique:asesores_comerciales,cedula,' . $asesor->id,
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'concesionario_id' => 'required|exists:concesionarios,id',
        ]);

        $asesor->update([
            'cedula' => $request->cedula,
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'concesionario_id' => $request->concesionario_id,
        ]);

        return redirect()
            ->route('asesores.index')
            ->with('success', 'Asesor comercial actualizado correctamente');
    }

    public function destroy(AsesorComercial $asesor)
    {
        $this->authorize('delete', $asesor);

        $asesor->delete();

        return back();
    }
}
