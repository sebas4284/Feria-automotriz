<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = Marca::orderBy('nombre')->get();

        return view('marcas.index', compact('marcas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:marcas,nombre',
        ]);

        Marca::create(['nombre' => trim($request->nombre)]);

        return redirect()
            ->route('marcas.index')
            ->with('success', 'Marca agregada correctamente');
    }

    public function destroy(Marca $marca)
    {
        $marca->delete();

        return back()->with('success', 'Marca eliminada correctamente');
    }
}
