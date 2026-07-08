<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\Lead;
use App\Models\Venta;

class DashboardController extends Controller
{
    public function index()
{
    // KPIs

    $totalClientes = Cliente::count();

    $totalVehiculos = Vehiculo::count();

    $totalLeads = Lead::count();

    $totalVentas = Venta::count();

    $ingresosMes = Venta::whereMonth(
        'fecha_venta',
        now()->month
    )->whereYear(
        'fecha_venta',
        now()->year
    )->sum('valor');

    // Pipeline

    $nuevos = Lead::where('estado', 'Nuevo')->latest()->take(10)->get();

    $contactados = Lead::where('estado', 'Contactado')->latest()->take(10)->get();

    $interesados = Lead::where('estado', 'Interesado')->latest()->take(10)->get();

    $citas = Lead::where('estado', 'Cita')->latest()->take(10)->get();

    $negociaciones = Lead::where('estado', 'Negociacion')->latest()->take(10)->get();

    $vendidos = Lead::where('estado', 'Vendido')->latest()->take(10)->get();

    return view(
        'dashboard',
        compact(
            'totalClientes',
            'totalVehiculos',
            'totalLeads',
            'totalVentas',
            'ingresosMes',

            'nuevos',
            'contactados',
            'interesados',
            'citas',
            'negociaciones',
            'vendidos'
        )
    );
}
}