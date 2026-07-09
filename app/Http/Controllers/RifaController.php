<?php

namespace App\Http\Controllers;

use App\Models\Venta;

class RifaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['comprador', 'vehiculo'])
            ->where('participa_experiencia', true)
            ->latest()
            ->get();

        return view('rifa.index', compact('ventas'));
    }
}
