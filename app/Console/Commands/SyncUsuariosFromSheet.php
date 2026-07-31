<?php

namespace App\Console\Commands;

use App\Services\UsuariosSheetImporter;
use Google\Client as GoogleClient;
use Google\Service\Sheets as GoogleSheets;
use Illuminate\Console\Command;

class SyncUsuariosFromSheet extends Command
{
    protected $signature = 'usuarios:sync-sheet {--tab= : Sincronizar solo la pestaña con este nombre exacto, en vez de todas}';

    protected $description = 'Sincroniza (solo lectura) concesionarios y usuarios desde el spreadsheet de onboarding (una pestaña por concesionario)';

    public function handle(UsuariosSheetImporter $importer): int
    {
        $credentialsPath = config('usuarios.sync.credentials_path');
        $spreadsheetId = config('usuarios.sync.spreadsheet_id');

        if (! $credentialsPath || ! $spreadsheetId) {
            $this->error('Faltan GOOGLE_SHEETS_CREDENTIALS_PATH y/o GOOGLE_SHEETS_USUARIOS_SPREADSHEET_ID en el .env');

            return self::FAILURE;
        }

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([GoogleSheets::SPREADSHEETS_READONLY]);

        $service = new GoogleSheets($client);

        $meta = $service->spreadsheets->get($spreadsheetId);

        $tabFiltro = $this->option('tab');

        $totales = ['concesionarios_creados' => 0, 'usuarios_creados' => 0, 'usuarios_actualizados' => 0, 'omitidos' => []];
        $pestanaEncontrada = false;

        foreach ($meta->getSheets() as $sheet) {
            $nombrePestana = $sheet->getProperties()->getTitle();
            $sheetTabId = $sheet->getProperties()->getSheetId();

            if ($tabFiltro !== null && strtolower(trim($nombrePestana)) !== strtolower(trim($tabFiltro))) {
                continue;
            }

            $pestanaEncontrada = true;

            $response = $service->spreadsheets_values->get($spreadsheetId, "'{$nombrePestana}'!A1:Z50");
            $values = $response->getValues() ?? [];

            $indiceEncabezado = $importer->buscarFilaEncabezado($values);

            if ($indiceEncabezado === null) {
                continue;
            }

            $headers = $values[$indiceEncabezado];
            $filas = array_slice($values, $indiceEncabezado + 1);

            $stats = $importer->import($nombrePestana, $sheetTabId, $headers, $filas);

            $totales['concesionarios_creados'] += $stats['concesionarios_creados'];
            $totales['usuarios_creados'] += $stats['usuarios_creados'];
            $totales['usuarios_actualizados'] += $stats['usuarios_actualizados'];
            $totales['omitidos'] = array_merge($totales['omitidos'], $stats['omitidos']);
        }

        if ($tabFiltro !== null && ! $pestanaEncontrada) {
            $this->error("No se encontró ninguna pestaña llamada \"{$tabFiltro}\" en el spreadsheet.");

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Sync completado: %d concesionarios creados, %d usuarios creados, %d actualizados, %d omitidos.',
            $totales['concesionarios_creados'],
            $totales['usuarios_creados'],
            $totales['usuarios_actualizados'],
            count($totales['omitidos'])
        ));

        foreach ($totales['omitidos'] as $motivo) {
            $this->warn('Omitido: ' . $motivo);
        }

        return self::SUCCESS;
    }
}
