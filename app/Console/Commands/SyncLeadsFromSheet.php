<?php

namespace App\Console\Commands;

use App\Services\LeadSheetImporter;
use Google\Client as GoogleClient;
use Google\Service\Sheets as GoogleSheets;
use Illuminate\Console\Command;

class SyncLeadsFromSheet extends Command
{
    protected $signature = 'leads:sync-sheet';

    protected $description = 'Sincroniza (solo lectura) los leads nuevos/actualizados desde las pestañas de Google Sheets configuradas';

    public function handle(LeadSheetImporter $importer): int
    {
        $credentialsPath = config('leads.sync.credentials_path');
        $spreadsheetId = config('leads.sync.spreadsheet_id');
        $sheetNames = config('leads.sync.sheet_names');

        if (! $credentialsPath || ! $spreadsheetId || empty($sheetNames)) {
            $this->error('Faltan GOOGLE_SHEETS_CREDENTIALS_PATH, GOOGLE_SHEETS_SPREADSHEET_ID y/o GOOGLE_SHEETS_SHEET_NAMES en el .env');

            return self::FAILURE;
        }

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([GoogleSheets::SPREADSHEETS_READONLY]);

        $service = new GoogleSheets($client);

        $totales = ['created' => 0, 'updated' => 0, 'unassigned' => 0, 'skipped' => 0];

        foreach ($sheetNames as $sheetName) {
            // Rango amplio (A:Z) para cubrir preguntas nuevas del formulario sin quedar
            // corto en columnas; el nombre va entre comillas simples porque puede traer
            // comas/paréntesis (ej. "Expocarshow 2026, Julio. NEW (v3)").
            $range = "'{$sheetName}'!A1:Z";
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues() ?? [];

            if (empty($values)) {
                continue;
            }

            $headers = array_shift($values);
            $stats = $importer->import($headers, $values);

            foreach ($stats as $key => $value) {
                $totales[$key] += $value;
            }
        }

        $this->info(sprintf(
            'Sync completado: %d creados, %d actualizados, %d sin asignar, %d omitidos.',
            $totales['created'],
            $totales['updated'],
            $totales['unassigned'],
            $totales['skipped']
        ));

        return self::SUCCESS;
    }
}
