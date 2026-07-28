<?php

namespace App\Console\Commands;

use App\Models\Concesionario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ExportarVehiculosPdf extends Command
{
    protected $signature = 'vehiculos:exportar-pdf {concesionario : Nombre exacto del concesionario}';

    protected $description = 'Genera un único PDF con la ficha de cada vehículo de un concesionario, ordenados por placa.';

    public function handle(): int
    {
        $nombre = $this->argument('concesionario');

        $concesionario = Concesionario::where('nombre', $nombre)->first();

        if (! $concesionario) {
            $this->error("No existe un concesionario con el nombre \"{$nombre}\".");

            return self::FAILURE;
        }

        $vehiculos = $concesionario->vehiculos()->orderBy('placa')->get();

        if ($vehiculos->isEmpty()) {
            $this->warn("El concesionario \"{$nombre}\" no tiene vehículos registrados. No se generó ningún archivo.");

            return self::SUCCESS;
        }

        $pdf = Pdf::loadView('vehiculos.ficha-pdf', ['vehiculos' => $vehiculos])
            ->setPaper('letter', 'portrait');

        $directorio = storage_path('app/exports');

        if (! is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $rutaArchivo = $directorio . DIRECTORY_SEPARATOR . sprintf(
            'vehiculos-%s-%s.pdf',
            Str::slug($concesionario->nombre),
            now()->format('Y-m-d')
        );

        $pdf->save($rutaArchivo);

        $this->info("PDF generado con {$vehiculos->count()} vehículo(s): {$rutaArchivo}");

        return self::SUCCESS;
    }
}
