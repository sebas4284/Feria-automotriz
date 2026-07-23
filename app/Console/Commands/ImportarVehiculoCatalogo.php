<?php

namespace App\Console\Commands;

use App\Models\VehiculoCatalogo;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportarVehiculoCatalogo extends Command
{
    protected $signature = 'vehiculos:importar-catalogo {ruta : Ruta al archivo Excel con el catálogo}';

    protected $description = 'Importa (deduplicando) las fichas técnicas marca/línea/versión de un Excel al catálogo de vehículos';

    /**
     * Alias de encabezados esperados en el Excel, por si el nombre exacto varía
     * (mayúsculas, tildes, espacios).
     */
    private const FIELD_ALIASES = [
        'marca' => ['marca'],
        'linea' => ['linea', 'línea'],
        'version' => ['version', 'versión'],
        'modelo' => ['modelo'],
        'clase_vehiculo' => ['clase de vehiculo', 'clase de vehículo'],
        'cc' => ['cc'],
        'combustible' => ['combustible'],
        'transmision' => ['transmision', 'transmisión'],
    ];

    public function handle(): int
    {
        $ruta = $this->argument('ruta');

        if (! file_exists($ruta)) {
            $this->error("No se encontró el archivo: {$ruta}");

            return self::FAILURE;
        }

        $spreadsheet = IOFactory::load($ruta);
        $filas = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if (empty($filas)) {
            $this->error('El archivo no tiene filas.');

            return self::FAILURE;
        }

        $headers = array_shift($filas);
        $columnMap = $this->buildColumnMap($headers);

        foreach (['marca', 'linea', 'version', 'clase_vehiculo', 'combustible', 'transmision'] as $campo) {
            if (! isset($columnMap[$campo])) {
                $this->error("No se encontró la columna para '{$campo}' en el encabezado del archivo.");

                return self::FAILURE;
            }
        }

        $fichas = [];

        foreach ($filas as $fila) {
            $get = fn (string $campo) => isset($columnMap[$campo])
                ? trim((string) ($fila[$columnMap[$campo]] ?? ''))
                : null;

            $clave = implode('|', [
                $get('marca'), $get('linea'), $get('version'),
                $get('clase_vehiculo'), $get('cc'), $get('combustible'), $get('transmision'),
            ]);

            $fichas[$clave] = [
                'marca' => $get('marca'),
                'linea' => $get('linea'),
                'version' => $get('version'),
                'clase_vehiculo' => $get('clase_vehiculo'),
                'cc' => $get('cc') !== '' ? (int) $get('cc') : null,
                'combustible' => $get('combustible'),
                'transmision' => $get('transmision'),
            ];
        }

        $creados = 0;
        $actualizados = 0;

        foreach ($fichas as $ficha) {
            $existente = VehiculoCatalogo::where($ficha)->exists();

            VehiculoCatalogo::updateOrCreate($ficha, $ficha);

            $existente ? $actualizados++ : $creados++;
        }

        $this->info(sprintf(
            'Importación completada: %d fichas únicas procesadas (%d creadas, %d ya existían).',
            count($fichas),
            $creados,
            $actualizados
        ));

        return self::SUCCESS;
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<string, int>
     */
    private function buildColumnMap(array $headers): array
    {
        $normalized = array_map(fn ($h) => mb_strtolower(trim((string) $h)), $headers);

        $map = [];

        foreach (self::FIELD_ALIASES as $campo => $aliases) {
            foreach ($aliases as $alias) {
                $index = array_search($alias, $normalized, true);

                if ($index !== false) {
                    $map[$campo] = $index;
                    break;
                }
            }
        }

        return $map;
    }
}
