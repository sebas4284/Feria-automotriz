<?php

namespace App\Console\Commands;

use App\Models\Concesionario;
use App\Models\Vehiculo;
use Illuminate\Console\Command;

class ReconciliarCupoVehiculos extends Command
{
    protected $signature = 'vehiculos:reconciliar-cupo {--dry-run}';

    protected $description = 'Demueve a "Fuera del área" los vehículos más recientes de cualquier concesionario por encima de su cupo, y promueve a "Dentro del área" los vehículos "Fuera del área" más antiguos de cada concesionario que ya tenga cupo libre (por ventas u otros cambios), respetando el cupo global de la feria.';

    public function handle(): int
    {
        $totalDemovidos = $this->demoverExcedentes();
        $this->info("Total demovidos por exceso de cupo: {$totalDemovidos}" . ($this->option('dry-run') ? ' (dry-run, no se guardó nada)' : ''));

        $cupoGlobal = config('feria.cupo_total');
        $usadoGlobal = Vehiculo::where('ubicacion', 'Dentro del área')->where('estado', '!=', 'Vendido')->count();
        $espacioGlobal = max(0, $cupoGlobal - $usadoGlobal);

        $this->info("Cupo global: {$usadoGlobal}/{$cupoGlobal} usados, espacio disponible: {$espacioGlobal}");

        if ($espacioGlobal <= 0) {
            $this->info('No hay espacio global disponible, nada que reconciliar.');

            return self::SUCCESS;
        }

        $concesionarios = Concesionario::where('activo', true)->get();
        $totalPromovidos = 0;

        foreach ($concesionarios as $concesionario) {
            if ($espacioGlobal <= 0) {
                break;
            }

            $usado = Vehiculo::where('concesionario_id', $concesionario->id)
                ->where('ubicacion', 'Dentro del área')
                ->where('estado', '!=', 'Vendido')
                ->count();

            $cupoConc = $concesionario->cupo_feria;
            $espacioConc = $cupoConc === null ? $espacioGlobal : max(0, $cupoConc - $usado);
            $aPromover = min($espacioConc, $espacioGlobal);

            if ($aPromover <= 0) {
                continue;
            }

            $candidatos = Vehiculo::where('concesionario_id', $concesionario->id)
                ->where('ubicacion', 'Fuera del área')
                ->where('estado', '!=', 'Vendido')
                ->orderBy('created_at')
                ->limit($aPromover)
                ->get();

            foreach ($candidatos as $vehiculo) {
                $sufijo = $this->option('dry-run') ? ' [dry-run]' : '';
                $this->info("{$vehiculo->placa} ({$concesionario->nombre}) -> Dentro del área{$sufijo}");

                if (! $this->option('dry-run')) {
                    $vehiculo->update(['ubicacion' => 'Dentro del área']);
                }

                $espacioGlobal--;
                $totalPromovidos++;
            }
        }

        $this->info("Total promovidos: {$totalPromovidos}" . ($this->option('dry-run') ? ' (dry-run, no se guardó nada)' : ''));

        return self::SUCCESS;
    }

    private function demoverExcedentes(): int
    {
        $totalDemovidos = 0;

        $concesionarios = Concesionario::where('activo', true)->whereNotNull('cupo_feria')->get();

        foreach ($concesionarios as $concesionario) {
            $usado = Vehiculo::where('concesionario_id', $concesionario->id)
                ->where('ubicacion', 'Dentro del área')
                ->where('estado', '!=', 'Vendido')
                ->count();

            $exceso = $usado - $concesionario->cupo_feria;

            if ($exceso <= 0) {
                continue;
            }

            $candidatos = Vehiculo::where('concesionario_id', $concesionario->id)
                ->where('ubicacion', 'Dentro del área')
                ->where('estado', '!=', 'Vendido')
                ->whereNull('ingresado_at')
                ->orderByDesc('created_at')
                ->limit($exceso)
                ->get();

            foreach ($candidatos as $vehiculo) {
                $sufijo = $this->option('dry-run') ? ' [dry-run]' : '';
                $this->info("{$vehiculo->placa} ({$concesionario->nombre}) -> Fuera del área (excedía cupo){$sufijo}");

                if (! $this->option('dry-run')) {
                    $vehiculo->update(['ubicacion' => 'Fuera del área']);
                }

                $totalDemovidos++;
            }

            if ($candidatos->count() < $exceso) {
                $faltante = $exceso - $candidatos->count();
                $this->warn("{$concesionario->nombre} sigue {$faltante} vehículo(s) por encima de su cupo: ya hicieron check-in en portería y no se pueden mover automáticamente.");
            }
        }

        return $totalDemovidos;
    }
}
