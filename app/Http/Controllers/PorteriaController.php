<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;

class PorteriaController extends Controller
{
    public function index()
    {
        $vehiculos = Vehiculo::with('concesionario')
            ->where('estado', '!=', 'Vendido')
            ->orderBy('placa')
            ->get();

        $totalDentro = $vehiculos->where('ubicacion', 'Dentro del área')->count();
        $totalIngresados = $vehiculos->where('ubicacion', 'Dentro del área')->whereNotNull('ingresado_at')->count();

        $porConcesionario = $vehiculos
            ->groupBy(fn (Vehiculo $v) => $v->concesionario?->nombre ?? 'Sin concesionario')
            ->map(function ($items, $nombre) {
                return (object) [
                    'nombre' => $nombre,
                    'dentro' => $items->where('ubicacion', 'Dentro del área')->count(),
                    'ingresados' => $items->where('ubicacion', 'Dentro del área')->whereNotNull('ingresado_at')->count(),
                    'vehiculos' => $items->sortBy('placa')->values(),
                ];
            })
            ->sortBy('nombre')
            ->values();

        return view('porteria.index', compact('vehiculos', 'totalDentro', 'totalIngresados', 'porConcesionario'));
    }

    public function marcarIngreso(Vehiculo $vehiculo)
    {
        if ($vehiculo->ubicacion !== 'Dentro del área') {
            return back()->with('error', 'Ese vehículo no está inscrito dentro del área.');
        }

        $vehiculo->update(['ingresado_at' => now()]);

        return back()->with('success', "{$vehiculo->placa} marcado como ingresado.");
    }

    public function quitarIngreso(Vehiculo $vehiculo)
    {
        $vehiculo->update(['ingresado_at' => null]);

        return back()->with('success', "Se deshizo el ingreso de {$vehiculo->placa}.");
    }
}
