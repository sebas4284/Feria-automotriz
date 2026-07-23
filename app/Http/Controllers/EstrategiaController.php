<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Carbon\Carbon;

class EstrategiaController extends Controller
{
    private const ETAPAS = ['Nuevo', 'Asignado', 'Contactado', 'Negociacion', 'Vendido', 'Perdido'];

    private const ESTADO_CONVERSION = 'Vendido';

    private const ESTADOS_TERMINALES = ['Vendido', 'Perdido'];

    public function index()
    {
        $totalLeads = Lead::count();

        $embudo = $this->conteoPorEtapa();

        $tasaConversionGlobal = $totalLeads > 0
            ? round(($embudo[self::ESTADO_CONVERSION] ?? 0) / $totalLeads * 100, 1)
            : 0;

        $conversionPorCanal = $this->conversionAgrupadaPor('platform', 'Sin canal');

        $conversionPorConcesionario = $this->conversionPorConcesionario();

        $conversionPorAsesor = $this->conversionPorAsesor();

        $leadsVencidos = Lead::vencido()
            ->with(['concesionario', 'asesorComercial'])
            ->orderBy('assigned_at')
            ->limit(20)
            ->get();

        $antiguedad = $this->antiguedadLeadsAbiertos();

        $patronesLlegada = $this->patronesDeLlegada();

        return view('estrategia.index', compact(
            'totalLeads',
            'embudo',
            'tasaConversionGlobal',
            'conversionPorCanal',
            'conversionPorConcesionario',
            'conversionPorAsesor',
            'leadsVencidos',
            'antiguedad',
            'patronesLlegada'
        ));
    }

    private function conteoPorEtapa(): array
    {
        $conteos = Lead::selectRaw('estado_gestion, COUNT(*) as total')
            ->groupBy('estado_gestion')
            ->pluck('total', 'estado_gestion');

        $embudo = [];

        foreach (self::ETAPAS as $etapa) {
            $embudo[$etapa] = (int) ($conteos[$etapa] ?? 0);
        }

        return $embudo;
    }

    /**
     * Agrupa leads por una columna simple (ej. platform) y calcula % de conversión a Vendido.
     */
    private function conversionAgrupadaPor(string $columna, string $etiquetaVacio)
    {
        return Lead::selectRaw("{$columna}, estado_gestion, COUNT(*) as total")
            ->groupBy($columna, 'estado_gestion')
            ->get()
            ->groupBy(fn (Lead $fila) => $fila->{$columna} ?: $etiquetaVacio)
            ->map(function ($filas, $etiqueta) {
                $total = $filas->sum('total');
                $vendidos = $filas->where('estado_gestion', self::ESTADO_CONVERSION)->sum('total');

                return (object) [
                    'etiqueta' => $etiqueta,
                    'total' => $total,
                    'vendidos' => $vendidos,
                    'tasa' => $total > 0 ? round($vendidos / $total * 100, 1) : 0,
                ];
            })
            ->sortByDesc('tasa')
            ->values();
    }

    private function conversionPorConcesionario()
    {
        return Lead::selectRaw('concesionario_id, estado_gestion, COUNT(*) as total')
            ->whereNotNull('concesionario_id')
            ->groupBy('concesionario_id', 'estado_gestion')
            ->with('concesionario:id,nombre')
            ->get()
            ->groupBy('concesionario_id')
            ->map(function ($filas) {
                $total = $filas->sum('total');
                $vendidos = $filas->where('estado_gestion', self::ESTADO_CONVERSION)->sum('total');

                return (object) [
                    'etiqueta' => $filas->first()->concesionario->nombre ?? 'Sin nombre',
                    'total' => $total,
                    'vendidos' => $vendidos,
                    'tasa' => $total > 0 ? round($vendidos / $total * 100, 1) : 0,
                ];
            })
            ->sortByDesc('tasa')
            ->values();
    }

    private function conversionPorAsesor()
    {
        return Lead::selectRaw('asesor_comercial_id, estado_gestion, COUNT(*) as total')
            ->whereNotNull('asesor_comercial_id')
            ->groupBy('asesor_comercial_id', 'estado_gestion')
            ->with('asesorComercial:id,nombre')
            ->get()
            ->groupBy('asesor_comercial_id')
            ->map(function ($filas) {
                $total = $filas->sum('total');
                $vendidos = $filas->where('estado_gestion', self::ESTADO_CONVERSION)->sum('total');

                return (object) [
                    'etiqueta' => $filas->first()->asesorComercial->nombre ?? 'Sin nombre',
                    'total' => $total,
                    'vendidos' => $vendidos,
                    'tasa' => $total > 0 ? round($vendidos / $total * 100, 1) : 0,
                ];
            })
            ->sortByDesc('tasa')
            ->values();
    }

    private function antiguedadLeadsAbiertos(): array
    {
        $abiertos = Lead::whereNotIn('estado_gestion', self::ESTADOS_TERMINALES)
            ->get(['estado_gestion', 'created_time', 'created_at']);

        $edadEnDias = fn (Lead $lead) => ($lead->created_time ?? $lead->created_at)->diffInDays(now());

        $promedioGlobal = $abiertos->isNotEmpty()
            ? round($abiertos->avg($edadEnDias), 1)
            : 0;

        $promedioPorEtapa = $abiertos->groupBy('estado_gestion')
            ->map(fn ($grupo) => round($grupo->avg($edadEnDias), 1));

        return [
            'global' => $promedioGlobal,
            'porEtapa' => $promedioPorEtapa,
        ];
    }

    private function patronesDeLlegada(): array
    {
        $leads = Lead::get(['created_time', 'created_at']);

        $fechas = $leads->map(fn (Lead $lead) => $lead->created_time ?? $lead->created_at)
            ->filter();

        $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        $conteoPorDiaSemana = $fechas->countBy(fn (Carbon $f) => $f->dayOfWeek);
        $porDiaSemana = collect($diasSemana)->mapWithKeys(fn ($nombre, $indice) => [
            $nombre => (int) ($conteoPorDiaSemana[$indice] ?? 0),
        ]);

        $conteoPorHora = $fechas->countBy(fn (Carbon $f) => $f->hour);
        $porHora = collect(range(0, 23))->mapWithKeys(fn ($hora) => [
            $hora => (int) ($conteoPorHora[$hora] ?? 0),
        ]);

        return [
            'porDiaSemana' => $porDiaSemana,
            'porHora' => $porHora,
        ];
    }
}
