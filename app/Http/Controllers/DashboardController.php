<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\Venta;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
{
    $user = $request->user();

    // KPIs

    $totalClientes = Cliente::visibleTo($user)->count();

    $totalVehiculos = Vehiculo::count();

    $totalVentas = Venta::visibleTo($user)->count();

    $ingresosMes = Venta::visibleTo($user)->whereMonth(
        'fecha_venta',
        now()->month
    )->whereYear(
        'fecha_venta',
        now()->year
    )->sum('valor');

    return view(
        'dashboard',
        compact(
            'totalClientes',
            'totalVehiculos',
            'totalVentas',
            'ingresosMes'
        )
    );
}
}
