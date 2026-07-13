<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Lead;
use App\Models\Vehiculo;
use App\Models\Venta;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const ESTADOS = ['Nuevo', 'Asignado', 'Contactado', 'Negociacion', 'Vendido', 'Perdido'];

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

        $data = compact('totalClientes', 'totalVehiculos', 'totalVentas', 'ingresosMes');

        if (! $user->isStaff()) {
            $data += $this->leadWidgets($user);
        }

        return view('dashboard', $data);
    }

    private function leadWidgets($user): array
    {
        $conteos = Lead::visibleTo($user)
            ->selectRaw('estado_gestion, COUNT(*) as total')
            ->groupBy('estado_gestion')
            ->pluck('total', 'estado_gestion');

        $pipeline = collect(self::ESTADOS)->mapWithKeys(
            fn (string $estado) => [$estado => (int) ($conteos[$estado] ?? 0)]
        );

        $leadsRecientesPorEstado = Lead::visibleTo($user)
            ->with(['concesionario', 'asesorComercial'])
            ->orderByRaw('COALESCE(created_time, created_at) DESC')
            ->limit(40)
            ->get()
            ->groupBy('estado_gestion');

        $leadsPorPlataforma = Lead::visibleTo($user)
            ->selectRaw('platform, COUNT(*) as total')
            ->groupBy('platform')
            ->get()
            ->groupBy(fn (Lead $row) => $row->platform ?: 'Desconocido')
            ->map(fn ($rows, $plataforma) => (object) ['plataforma' => $plataforma, 'total' => $rows->sum('total')])
            ->sortByDesc('total')
            ->values();

        $leadsPorMontoInteres = Lead::visibleTo($user)
            ->whereNotNull('monto_interes_aprobar')
            ->where('monto_interes_aprobar', '!=', '')
            ->selectRaw('monto_interes_aprobar, COUNT(*) as total')
            ->groupBy('monto_interes_aprobar')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $diasDelMes = now()->daysInMonth;
        $conteosPorDia = Lead::visibleTo($user)
            ->whereRaw('COALESCE(created_time, created_at) BETWEEN ? AND ?', [now()->startOfMonth(), now()->endOfMonth()])
            ->get(['created_time', 'created_at'])
            ->groupBy(fn (Lead $lead) => ($lead->created_time ?? $lead->created_at)->format('Y-m-d'))
            ->map->count();

        $leadsPorDia = collect(range(1, $diasDelMes))->mapWithKeys(function (int $dia) use ($conteosPorDia) {
            $fecha = now()->startOfMonth()->addDays($dia - 1)->format('Y-m-d');

            return [$fecha => (int) ($conteosPorDia[$fecha] ?? 0)];
        });

        return compact('pipeline', 'leadsRecientesPorEstado', 'leadsPorPlataforma', 'leadsPorMontoInteres', 'leadsPorDia');
    }
}
