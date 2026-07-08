<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Concesionario;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::with('concesionario')
            ->latest()
            ->get();

        return view(
            'leads.index',
            compact('leads')
        );
    }

    public function create()
    {
        return view('leads.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'telefono' => 'required'
        ]);

        $concesionarios = Concesionario::where('activo', true)->get();

        $pool = [];

        foreach ($concesionarios as $concesionario) {

            for ($i = 0; $i < $concesionario->peso_asignacion; $i++) {

                $pool[] = $concesionario;

            }
        }

        $concesionarioAsignado = $pool[array_rand($pool)];

        Lead::create([

            'nombre' => $request->nombre,

            'telefono' => $request->telefono,

            'email' => $request->email,

            'ciudad' => $request->ciudad,

            'vehiculo_interes' => $request->vehiculo_interes,

            'estado' => 'Nuevo',

            'concesionario_id' => $concesionarioAsignado->id,

            'fecha_asignacion' => now(),

            'reasignaciones' => 0

        ]);

        return redirect()
            ->route('leads.index')
            ->with(
                'success',
                'Lead asignado a ' . $concesionarioAsignado->nombre
            );
    }
    public function show(Lead $lead)
    {
        return view(
            'leads.show',
            compact('lead')
        );
    }
    public function edit(Lead $lead)
    {
        $concesionarios = Concesionario::where('activo', true)->get();

        return view(
            'leads.edit',
            compact(
                'lead',
                'concesionarios'
            )
        );
    }
    public function update(Request $request, Lead $lead)
    {
        $concesionarioAnterior = $lead->concesionario_id;

        $lead->estado = $request->estado;
        $lead->observacion = $request->observacion;
        $lead->ultima_gestion = now();

        if ($concesionarioAnterior != $request->concesionario_id) {

            $lead->concesionario_id = $request->concesionario_id;

            $lead->reasignaciones += 1;

            $lead->fecha_asignacion = now();

            $lead->estado = 'Reasignado';
        }

        $lead->save();

        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead actualizado correctamente');
    }
}