<?php

namespace App\Http\Controllers;

use App\Models\AsesorComercial;
use App\Models\Concesionario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with(['concesionario', 'asesorComercial.concesionario'])->latest()->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $concesionarios = Concesionario::orderBy('nombre')->get();
        $asesoresComerciales = AsesorComercial::orderBy('nombre')->get();

        return view('usuarios.create', compact('concesionarios', 'asesoresComerciales'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'rol' => 'required|in:admin,concesionario,asesor,staff,porteria',
            'concesionario_id' => 'nullable|required_if:rol,concesionario|exists:concesionarios,id',
            'asesor_comercial_id' => 'nullable|required_if:rol,asesor|exists:asesores_comerciales,id',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol' => $validated['rol'],
            'concesionario_id' => $validated['rol'] === 'concesionario' ? $validated['concesionario_id'] : null,
            'asesor_comercial_id' => $validated['rol'] === 'asesor' ? $validated['asesor_comercial_id'] : null,
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente');
    }

    public function edit(User $usuario)
    {
        $concesionarios = Concesionario::orderBy('nombre')->get();
        $asesoresComerciales = AsesorComercial::orderBy('nombre')->get();

        return view('usuarios.edit', compact('usuario', 'concesionarios', 'asesoresComerciales'));
    }

    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:8',
            'rol' => 'required|in:admin,concesionario,asesor,staff,porteria',
            'concesionario_id' => 'nullable|required_if:rol,concesionario|exists:concesionarios,id',
            'asesor_comercial_id' => 'nullable|required_if:rol,asesor|exists:asesores_comerciales,id',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'rol' => $validated['rol'],
            'concesionario_id' => $validated['rol'] === 'concesionario' ? $validated['concesionario_id'] : null,
            'asesor_comercial_id' => $validated['rol'] === 'asesor' ? $validated['asesor_comercial_id'] : null,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $usuario->update($data);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $usuario)
    {
        $usuario->delete();

        return back()->with('success', 'Usuario eliminado correctamente');
    }
}
