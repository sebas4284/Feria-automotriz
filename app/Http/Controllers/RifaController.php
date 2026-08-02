<?php

namespace App\Http\Controllers;

use App\Exports\RifaExport;
use App\Models\Venta;
use Maatwebsite\Excel\Facades\Excel;

class RifaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['comprador', 'vehiculo', 'asesorComercial', 'concesionarioVende'])
            ->where('participa_experiencia', true)
            ->latest()
            ->get();

        return view('rifa.index', compact('ventas'));
    }

    public function exportar()
    {
        $ventas = Venta::with(['comprador', 'vehiculo', 'asesorComercial', 'concesionarioVende'])
            ->where('participa_experiencia', true)
            ->latest()
            ->get();

        return Excel::download(new RifaExport($ventas), 'rifa-' . now()->format('Y-m-d') . '.xlsx');
    }
}
