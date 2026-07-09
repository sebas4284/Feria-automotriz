<?php

namespace App\Console\Commands;

use App\Services\LeadSheetImporter;
use Google\Client as GoogleClient;
use Google\Service\Sheets as GoogleSheets;
use Illuminate\Console\Command;

class SyncLeadsFromSheet extends Command
{
    protected $signature = 'leads:sync-sheet';

    protected $description = 'Sincroniza (solo lectura) los leads nuevos/actualizados desde el Google Sheet configurado';

    public function handle(LeadSheetImporter $importer): int
    {
        $credentialsPath = config('leads.sync.credentials_path');
        $spreadsheetId = config('leads.sync.spreadsheet_id');
        $sheetName = config('leads.sync.sheet_name');

        if (! $credentialsPath || ! $spreadsheetId) {
            $this->error('Faltan GOOGLE_SHEETS_CREDENTIALS_PATH y/o GOOGLE_SHEETS_SPREADSHEET_ID en el .env');

            return self::FAILURE;
        }

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([GoogleSheets::SPREADSHEETS_READONLY]);

        $service = new GoogleSheets($client);

        // Empieza en la fila 2 para saltar el encabezado. Columnas A..R = 18 campos.
        $range = "{$sheetName}!A2:R";
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $rows = $response->getValues() ?? [];

        $stats = $importer->import($rows);

        $this->info(sprintf(
            'Sync completado: %d creados, %d actualizados, %d sin asignar, %d omitidos.',
            $stats['created'],
            $stats['updated'],
            $stats['unassigned'],
            $stats['skipped']
        ));

        return self::SUCCESS;
    }
}
