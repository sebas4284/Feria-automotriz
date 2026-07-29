<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Lead;
use App\Models\Venta;
use Illuminate\Http\Request;

class EstadisticasController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $ventasPorConcesionario = $isAdmin
            ? Venta::selectRaw('concesionario_vende_id, COUNT(*) as total_ventas, SUM(valor) as total_ingresos')
                ->groupBy('concesionario_vende_id')
                ->with('concesionarioVende')
                ->get()
            : null;

        $leadsPorConcesionario = $isAdmin
            ? Lead::selectRaw('concesionario_id, COUNT(*) as total')
                ->groupBy('concesionario_id')
                ->with('concesionario')
                ->orderByDesc('total')
                ->get()
            : null;

        $leadsPorEstado = Lead::query()->visibleTo($user)
            ->selectRaw('estado_gestion, COUNT(*) as total')
            ->groupBy('estado_gestion')
            ->pluck('total', 'estado_gestion');

        $leadsVendidos = $leadsPorEstado['Vendido'] ?? 0;
        $totalLeads = $leadsPorEstado->sum();

        $ventasScope = Venta::query()->visibleTo($user);
        $totalVentas = (clone $ventasScope)->count();
        $totalIngresos = (clone $ventasScope)->sum('valor');
        $totalRifa = (clone $ventasScope)->where('participa_experiencia', true)->count();

        $totalClientes = Cliente::query()->visibleTo($user)->count();

        return view('estadisticas.index', compact(
            'isAdmin',
            'ventasPorConcesionario',
            'leadsPorConcesionario',
            'leadsPorEstado',
            'leadsVendidos',
            'totalLeads',
            'totalVentas',
            'totalIngresos',
            'totalRifa',
            'totalClientes'
        ));
    }
}
