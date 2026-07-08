<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cliente::latest();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        $clientes = $query->get();
        $totalLeadsNuevos    = $clientes->where('estado', 'Nuevo')->count();
        $totalVentasCerradas = $clientes->where('estado', 'Vendido')->count();

        return view('clientes.index', compact('clientes', 'totalLeadsNuevos', 'totalVentasCerradas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clientes,email',
            'telefono' => 'nullable|string|max:20',
            'ciudad' => 'nullable|string|max:255',
            'vehiculo_interes' => 'nullable|string|max:255',
            'presupuesto' => 'nullable|numeric',
            'estado' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        Cliente::create([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'ciudad' => $request->ciudad,
            'vehiculo_interes' => $request->vehiculo_interes,
            'presupuesto' => $request->presupuesto,
            'estado' => $request->estado,
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
        return view('clientes.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clientes,email,' . $cliente->id,
            'telefono' => 'nullable|string|max:20',
            'ciudad' => 'nullable|string|max:255',
            'vehiculo_interes' => 'nullable|string|max:255',
            'presupuesto' => 'nullable|numeric',
            'estado' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        $cliente->update([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'ciudad' => $request->ciudad,
            'vehiculo_interes' => $request->vehiculo_interes,
            'presupuesto' => $request->presupuesto,
            'estado' => $request->estado,
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
        $cliente->delete();

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente eliminado correctamente');
    }
}